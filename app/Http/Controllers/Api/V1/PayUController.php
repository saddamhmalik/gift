<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\Woohoo\WoohooOrderException;
use App\Http\Controllers\Api\Controller;
use App\Http\Resources\V1\OrderResource;
use App\Jobs\CreditLoyaltyForOrderJob;
use App\Models\Order;
use App\Models\Setting;
use App\Repositories\OrderRepository;
use App\Services\Loyalty\LoyaltyService;
use App\Services\Order\FulfillOrderViaWoohooService;
use App\Services\Order\OrderService;
use App\Services\Payment\PayUService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PayUController extends Controller
{
    public function __construct(
        protected PayUService $payUService,
        protected OrderService $orderService,
        protected OrderRepository $orderRepository,
        protected FulfillOrderViaWoohooService $fulfillService,
        protected LoyaltyService $loyaltyService,
    ) {}

    /**
     * POST /api/v1/payment/initiate
     * Authenticated. Creates/fetches order, applies loyalty points if requested, returns PayU params.
     */
    public function initiate(Request $request): JsonResponse
    {
        $request->validate([
            'order_token' => 'nullable|string',
            'order_id' => 'nullable|integer|exists:orders,id',
            'points_to_use' => 'nullable|numeric|min:0',
        ]);

        $user = $request->user();
        $token = $request->input('order_token') ?? $request->header('X-Order-Token');

        // Resolve the order
        $order = null;
        if ($request->filled('order_id')) {
            $order = $this->orderRepository->findByIdAndUser((int) $request->order_id, $user);
        }
        if (! $order) {
            $order = $this->orderService->resolveOrder($token, $user);
        }

        if (! $order) {
            return $this->error('Order not found. Create an order first.', 404);
        }

        $order = $this->orderService->getOrder($order);

        if (! $order->items->count()) {
            return $this->error('Order has no item. Set an item before initiating payment.', 422);
        }

        if (! $order->total_amount || $order->total_amount <= 0) {
            return $this->error('Order total is invalid.', 422);
        }

        // Handle loyalty points redemption request
        $pointsToUse = (float) ($request->input('points_to_use', 0));
        if ($pointsToUse > 0) {
            $balance = $this->loyaltyService->balance($user);
            $maxPerOrder = (float) Setting::get('loyalty.max_redeem_per_order', config('loyalty.max_redeem_per_order', 500));
            $minRedeem = (float) Setting::get('loyalty.min_redeem', config('loyalty.min_redeem', 1));

            // Cap: min(requested, user balance, fixed per-order cap, order total)
            $caps = [$balance, (float) $order->total_amount];
            if ($maxPerOrder > 0) {
                $caps[] = $maxPerOrder;
            }
            $pointsToUse = round(min($pointsToUse, ...$caps), 2);

            if ($pointsToUse < $minRedeem) {
                $pointsToUse = 0;
            }
        }

        // Persist points_used on the order so PayUService can reduce the charge amount
        if ($order->points_used != $pointsToUse) {
            $order->update(['points_used' => $pointsToUse]);
            $order->refresh();
        }

        $params = $this->payUService->buildPaymentParams($order, $user);

        return $this->success([
            'order' => new OrderResource($order),
            'payu_params' => $params,
            'points_applied' => $pointsToUse,
            'amount_to_pay' => (float) $params['amount'],
        ], 'Payment initiated');
    }

    /**
     * POST /api/v1/payment/payu/success
     * PayU posts here after successful payment. Verifies hash, fulfills order via Woohoo,
     * debits redeemed points immediately, schedules earned points after the configured delay.
     */
    public function payuSuccess(Request $request): RedirectResponse
    {
        $params = $request->all();
        $frontendUrl = rtrim(config('payu.frontend_url'), '/');
        $orderId = $params['udf1'] ?? null;

        Log::info('PayU success callback received', [
            'txnid' => $params['mihpayid'] ?? $params['txnid'] ?? null,
            'status' => $params['status'] ?? null,
            'order_id' => $orderId,
        ]);

        // Verify hash
        if (! $this->payUService->verifyResponseHash($params)) {
            Log::error('PayU success: hash verification failed', ['params' => $params]);

            return redirect($frontendUrl.'/payment/failure?reason=hash_mismatch');
        }

        if (($params['status'] ?? '') !== 'success') {
            Log::warning('PayU success URL called with non-success status', ['status' => $params['status']]);

            return redirect($frontendUrl.'/payment/failure?reason='.urlencode($params['status'] ?? 'failed'));
        }

        // Resolve order
        $order = $orderId
            ? $this->orderRepository->find((int) $orderId)
            : $this->orderRepository->findByToken($params['txnid'] ?? '');

        if (! $order) {
            Log::error('PayU success: order not found', ['udf1' => $orderId, 'txnid' => $params['txnid'] ?? null]);

            return redirect($frontendUrl.'/payment/failure?reason=order_not_found');
        }

        // Store PayU's transaction ID + paid amount immediately — needed for refunds.
        // Do this before the idempotency check so even duplicate callbacks keep the value fresh.
        $mihpayid = $params['mihpayid'] ?? null;
        $paidAmount = isset($params['amount']) ? (float) $params['amount'] : null;
        if ($mihpayid && empty($order->payu_mihpayid)) {
            $order->update([
                'payu_mihpayid' => $mihpayid,
                'payu_paid_amount' => $paidAmount,
            ]);
            $order->refresh();
        }

        // Idempotency — already fulfilled
        if ($order->status !== Order::STATUS_PENDING) {
            return redirect($frontendUrl.'/orders/'.$order->id.'?payment=success');
        }

        try {
            $user = $order->user;
            $rawPhone = $user?->phone ?: ($params['phone'] ?? '');
            $billing = [
                'email' => $params['email'] ?? $user?->email,
                'telephone' => $rawPhone,
                'name' => $params['firstname'] ?? $user?->name,
                'firstname' => $params['firstname'] ?? $user?->first_name ?? $user?->name,
            ];

            // ── Phase 1: idempotency check (short-lived lock, no external calls) ────────
            // Decide whether Woohoo needs to be called for this payment.
            $alreadyDone = false;
            $shouldFulfill = false;

            DB::transaction(function () use ($order, &$alreadyDone, &$shouldFulfill) {
                $locked = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

                if ($locked->status !== Order::STATUS_PENDING) {
                    Log::info('PayU success: duplicate callback — order already processed', [
                        'order_id' => $locked->id,
                        'status' => $locked->status,
                    ]);
                    $alreadyDone = true;

                    return;
                }

                // woohoo_refno set means Woohoo already received this order (e.g. prior attempt or timeout recovery)
                $shouldFulfill = empty($locked->woohoo_refno);

                if (! $shouldFulfill) {
                    Log::info('PayU success: skipping Woohoo — refno already set', ['order_id' => $locked->id]);
                }
            });

            if ($alreadyDone) {
                return redirect($frontendUrl.'/orders/'.$order->id.'?payment=success');
            }

            // ── Phase 2: Woohoo fulfillment OUTSIDE any transaction ──────────────────────
            // This guarantees that on ConnectionException (client timeout),
            // WoohooOrderService's $order->update(['woohoo_refno' => $refno]) is committed
            // to the DB immediately and is never rolled back — so WoohooOrderPostTimeoutJob
            // can find the refno and recover via the Order Status API.
            if ($shouldFulfill) {
                $order->refresh();
                $this->fulfillService->fulfill($order, $billing, []);
            }

            // ── Phase 3: loyalty debit now; earned points credited after configured delay ─
            DB::transaction(function () use ($order, $user) {
                $locked = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
                $locked->refresh();

                // Debit redeemed points now (payment confirmed)
                if ($user && (float) $locked->points_used > 0) {
                    $this->loyaltyService->debitForOrder($locked, (float) $locked->points_used);
                }
            });

            if ($user) {
                $delayHours = max(0, (int) config('loyalty.credit_delay_hours', 24));
                CreditLoyaltyForOrderJob::dispatch($order->fresh())
                    ->delay(now()->addHours($delayHours));
            }

            Log::info('PayU success: order fulfilled + loyalty processed', ['order_id' => $order->id]);

            return redirect($frontendUrl.'/orders/'.$order->id.'?payment=success');

        } catch (WoohooOrderException $e) {
            Log::error('PayU success: Woohoo fulfillment failed', [
                'order_id' => $order->id,
                'message' => $e->getMessage(),
                'woohoo_code' => $e->getWoohooCode(),
            ]);

            // CLIENT_TIMEOUT: we don't know if Woohoo received the order.
            // WoohooOrderPostTimeoutJob will poll Status API; it will dispatch RefundOrderJob
            // if Status API returns CANCELED. Do NOT refund immediately.
            if ($e->getWoohooCode() !== 'CLIENT_TIMEOUT') {
                // Mark delivery failed immediately so the order page shows the correct state
                // before RefundOrderJob runs (which also sets this, but runs async).
                $order->update([
                    'delivery_status' => \App\Services\Woohoo\WoohooOrderService::DELIVERY_STATUS_FAILED,
                    'status' => Order::STATUS_CANCELLED,
                ]);
                \App\Jobs\RefundOrderJob::dispatch($order, $e->getMessage());
            }

            return redirect($frontendUrl.'/orders/'.$order->id.'?payment=paid&fulfillment=failed');
        } catch (\Throwable $e) {
            Log::error('PayU success: unexpected error', [
                'order_id' => $order->id,
                'message' => $e->getMessage(),
            ]);

            return redirect($frontendUrl.'/orders/'.$order->id.'?payment=paid&fulfillment=error');
        }
    }

    /**
     * POST /api/v1/payment/payu/failure
     * PayU posts here when payment fails/is cancelled.
     * Resets points_used on the order so the user's balance isn't locked.
     */
    public function payuFailure(Request $request): RedirectResponse
    {
        $params = $request->all();
        $frontendUrl = rtrim(config('payu.frontend_url'), '/');
        $orderId = $params['udf1'] ?? null;

        Log::warning('PayU failure callback', [
            'txnid' => $params['txnid'] ?? null,
            'status' => $params['status'] ?? null,
            'order_id' => $orderId,
            'error' => $params['error_Message'] ?? $params['field9'] ?? null,
        ]);

        // Reset points_used so the user's balance isn't reserved for a failed order
        if ($orderId) {
            $order = $this->orderRepository->find((int) $orderId);
            if ($order && $order->status === \App\Models\Order::STATUS_PENDING) {
                $order->update(['points_used' => 0]);
            }
        }

        $query = http_build_query([
            'reason' => $params['error_Message'] ?? $params['status'] ?? 'Payment failed',
            'order_id' => $orderId,
        ]);

        return redirect($frontendUrl.'/payment/failure?'.$query);
    }
}
