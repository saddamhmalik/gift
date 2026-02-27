<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\Woohoo\WoohooOrderException;
use App\Http\Controllers\Api\Controller;
use App\Http\Resources\V1\OrderResource;
use App\Repositories\OrderRepository;
use App\Services\Order\FulfillOrderViaWoohooService;
use App\Services\Order\OrderService;
use App\Services\Payment\PayUService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PayUController extends Controller
{
    public function __construct(
        protected PayUService $payUService,
        protected OrderService $orderService,
        protected OrderRepository $orderRepository,
        protected FulfillOrderViaWoohooService $fulfillService,
    ) {}

    /**
     * POST /api/v1/payment/initiate
     * Authenticated. Creates/fetches order, sets item if not set, returns PayU params + hash.
     */
    public function initiate(Request $request): JsonResponse
    {
        $request->validate([
            'order_token'  => 'nullable|string',
            'order_id'     => 'nullable|integer|exists:orders,id',
        ]);

        $user  = $request->user();
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

        $params = $this->payUService->buildPaymentParams($order, $user);

        return $this->success([
            'order'       => new OrderResource($order),
            'payu_params' => $params,
        ], 'Payment initiated');
    }

    /**
     * POST /api/v1/payment/payu/success
     * PayU posts here after successful payment. Verifies hash, fulfills order via Woohoo, redirects to frontend.
     */
    public function payuSuccess(Request $request): RedirectResponse
    {
        $params    = $request->all();
        $frontendUrl = rtrim(config('payu.frontend_url'), '/');
        $orderId   = $params['udf1'] ?? null;

        Log::info('PayU success callback received', [
            'txnid'    => $params['mihpayid'] ?? $params['txnid'] ?? null,
            'status'   => $params['status'] ?? null,
            'order_id' => $orderId,
        ]);

        // Verify hash
        if (! $this->payUService->verifyResponseHash($params)) {
            Log::error('PayU success: hash verification failed', ['params' => $params]);
            return redirect($frontendUrl . '/payment/failure?reason=hash_mismatch');
        }

        if (($params['status'] ?? '') !== 'success') {
            Log::warning('PayU success URL called with non-success status', ['status' => $params['status']]);
            return redirect($frontendUrl . '/payment/failure?reason=' . urlencode($params['status'] ?? 'failed'));
        }

        // Resolve order
        $order = $orderId
            ? $this->orderRepository->find((int) $orderId)
            : $this->orderRepository->findByToken($params['txnid'] ?? '');

        if (! $order) {
            Log::error('PayU success: order not found', ['udf1' => $orderId, 'txnid' => $params['txnid'] ?? null]);
            return redirect($frontendUrl . '/payment/failure?reason=order_not_found');
        }

        // Idempotency — already fulfilled
        if ($order->status !== \App\Models\Order::STATUS_PENDING) {
            return redirect($frontendUrl . '/orders/' . $order->id . '?payment=success');
        }

        try {
            $user = $order->user;
            // Prefer user's stored phone (already validated at registration);
            // fall back to what PayU echoed back — the payload builder will E164-sanitize it.
            $rawPhone = $user?->phone ?: ($params['phone'] ?? '');
            $billing = [
                'email'     => $params['email']    ?? $user?->email,
                'telephone' => $rawPhone,
                'name'      => $params['firstname'] ?? $user?->name,
                'firstname' => $params['firstname'] ?? $user?->first_name ?? $user?->name,
            ];

            $this->fulfillService->fulfill($order, $billing, [], false);

            Log::info('PayU success: order fulfilled', ['order_id' => $order->id]);
            return redirect($frontendUrl . '/orders/' . $order->id . '?payment=success');
        } catch (WoohooOrderException $e) {
            Log::error('PayU success: Woohoo fulfillment failed', [
                'order_id'     => $order->id,
                'message'      => $e->getMessage(),
                'woohoo_code'  => $e->getWoohooCode(),
            ]);
            return redirect($frontendUrl . '/orders/' . $order->id . '?payment=paid&fulfillment=failed');
        } catch (\Throwable $e) {
            Log::error('PayU success: unexpected error', [
                'order_id' => $order->id,
                'message'  => $e->getMessage(),
            ]);
            return redirect($frontendUrl . '/orders/' . $order->id . '?payment=paid&fulfillment=error');
        }
    }

    /**
     * POST /api/v1/payment/payu/failure
     * PayU posts here when payment fails/is cancelled.
     */
    public function payuFailure(Request $request): RedirectResponse
    {
        $params      = $request->all();
        $frontendUrl = rtrim(config('payu.frontend_url'), '/');
        $orderId     = $params['udf1'] ?? null;

        Log::warning('PayU failure callback', [
            'txnid'    => $params['txnid'] ?? null,
            'status'   => $params['status'] ?? null,
            'order_id' => $orderId,
            'error'    => $params['error_Message'] ?? $params['field9'] ?? null,
        ]);

        $query = http_build_query([
            'reason'   => $params['error_Message'] ?? $params['status'] ?? 'Payment failed',
            'order_id' => $orderId,
        ]);

        return redirect($frontendUrl . '/payment/failure?' . $query);
    }
}
