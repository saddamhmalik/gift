<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\Woohoo\WoohooOrderException;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Repositories\OrderRepository;
use Illuminate\Support\Facades\Log;
use App\Services\Order\FulfillOrderViaWoohooService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Placeholder for payment gateway success callback.
 * When your payment gateway confirms payment, call this (or your own handler that uses FulfillOrderViaWoohooService).
 */
class WebhookPaymentController extends Controller
{
    public function __construct(
        protected FulfillOrderViaWoohooService $fulfillService,
        protected OrderRepository $orderRepository
    ) {}

    /**
     * Fulfill order via Woohoo (SVC) after payment success.
     * Expects order_id or order_token; billing email/telephone required on order or in body.
     */
    public function paymentSuccess(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_id' => 'nullable|integer|exists:orders,id',
            'order_token' => 'nullable|string',
            'sync_only' => 'nullable|boolean',
            'billing' => 'nullable|array',
            'billing.email' => 'nullable|email',
            'billing.telephone' => 'nullable|string',
            'billing.name' => 'nullable|string',
            'address' => 'nullable|array',
        ]);

        $order = $this->resolveOrder($validated);
        if (! $order) {
            throw ValidationException::withMessages(['order_id' => ['Order not found. Provide order_id or order_token.']]);
        }

        try {
            $result = $this->fulfillService->fulfill(
                $order,
                $validated['billing'] ?? [],
                $validated['address'] ?? [],
                (bool) ($validated['sync_only'] ?? false)
            );
            return response()->json([
                'message' => 'Order submitted to Woohoo.',
                'refno' => $result['refno'],
                'woohoo_order_id' => $result['woohoo_order_id'] ?? null,
                'status' => $result['status'],
                'sync' => $result['sync'],
                'poll_dispatched' => $result['poll_dispatched'] ?? false,
            ], $result['status'] === 201 ? 201 : 200);
        } catch (WoohooOrderException $e) {
            Log::error('Payment webhook: Woohoo order failed', [
                'order_id' => $order->id ?? null,
                'message' => $e->getMessage(),
                'woohoo_code' => $e->getWoohooCode(),
                'woohoo_response' => $e->getWoohooResponse(),
            ]);
            return response()->json([
                'message' => $e->getMessage(),
                'woohoo_code' => $e->getWoohooCode(),
            ], 422);
        } catch (\InvalidArgumentException $e) {
            Log::error('Payment webhook: validation failed', ['message' => $e->getMessage()]);
            throw ValidationException::withMessages(['order' => [$e->getMessage()]]);
        } catch (\Throwable $e) {
            Log::error('Payment webhook: unexpected error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    protected function resolveOrder(array $validated): ?Order
    {
        if (! empty($validated['order_id'])) {
            return $this->orderRepository->find((int) $validated['order_id']);
        }
        if (! empty($validated['order_token'])) {
            return $this->orderRepository->findByToken($validated['order_token']);
        }
        return null;
    }
}
