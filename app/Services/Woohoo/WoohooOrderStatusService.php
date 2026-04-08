<?php

namespace App\Services\Woohoo;

use App\Jobs\FetchActivatedCardsJob;
use App\Models\Order;
use App\Services\WoohooClient;
use Illuminate\Support\Facades\Log;

class WoohooOrderStatusService
{
    /** Order Details API terminal statuses (no need to poll further) */
    public const STATUS_COMPLETE = 'COMPLETE';
    public const STATUS_CANCELED = 'CANCELED';
    public const TERMINAL_STATUSES = [self::STATUS_COMPLETE, self::STATUS_CANCELED, 'COMPLETED', 'CANCELLED'];

    /** Error code: Order Not Available (e.g. archived or invalid) */
    public const ERROR_ORDER_NOT_AVAILABLE = 5320;

    public function __construct(
        protected WoohooClient $client,
        protected WoohooOrderService $woohooOrderService,
        protected WoohooActivatedCardsService $activatedCardsService,
    ) {}

    /**
     * Fetch order details from Woohoo Order Details API: GET /rest/v3/orders/{order_id}.
     * Returns full response: orderId, refno, status, statusLabel, products, address, billing, payments, cards, etc.
     *
     * @return array{orderId?: string, refno?: string, status?: string, statusLabel?: string, products?: array, address?: array, billing?: array, payments?: array, cards?: array, ...}
     */
    public function getOrderDetails(string $woohooOrderId): array
    {
        $basePath = rtrim(config('woohoo.endpoints.order_status', '/rest/v3/orders'), '/');
        $path = $basePath . '/' . $woohooOrderId;
        $response = $this->client->get($path);

        if (! $response->successful()) {
            $body = $response->json() ?? [];
            $code = $body['code'] ?? null;
            $message = $body['message'] ?? $response->body();
            Log::warning('Woohoo Order Details API failed', [
                'woohoo_order_id' => $woohooOrderId,
                'http_status' => $response->status(),
                'code' => $code,
                'message' => $message,
            ]);
            return [
                'status' => $code === self::ERROR_ORDER_NOT_AVAILABLE ? 'NOT_AVAILABLE' : 'UNKNOWN',
                'errorCode' => $code,
                'message' => $message,
            ];
        }

        return $response->json() ?? [];
    }

    /**
     * Alias for getOrderDetails for backwards compatibility.
     *
     * @return array{status?: string, orderId?: string, ...}
     */
    public function getOrderStatus(string $woohooOrderId): array
    {
        return $this->getOrderDetails($woohooOrderId);
    }

    /**
     * Apply one Order Details (Status) API response: update order; on COMPLETE, call Activated Cards API.
     *
     * @return string complete|canceled|processing|unavailable|unknown
     */
    public function applyOrderDetailsSnapshot(Order $order, array $data): string
    {
        $woohooOrderId = $order->woohoo_order_id;
        if (empty($woohooOrderId)) {
            return 'unknown';
        }

        $status = (string) ($data['status'] ?? $data['orderStatus'] ?? 'UNKNOWN');
        $status = strtoupper($status);

        if ($status === 'NOT_AVAILABLE' || isset($data['errorCode'])) {
            $order->update([
                'woohoo_response' => array_merge($order->woohoo_response ?? [], ['lastStatus' => $data]),
                'delivery_status' => WoohooOrderService::DELIVERY_STATUS_FAILED,
                'status' => Order::STATUS_CANCELLED,
            ]);

            return 'unavailable';
        }

        $deliveryStatus = $this->mapToDeliveryStatus($status);
        $orderStatus = $this->mapToOrderStatus($status);

        $order->update([
            'woohoo_response' => array_merge($order->woohoo_response ?? [], ['lastStatus' => $data]),
            'delivery_status' => $deliveryStatus,
            'status' => $orderStatus,
        ]);

        if (in_array($status, self::TERMINAL_STATUSES, true)) {
            if ($status === self::STATUS_COMPLETE || $status === 'COMPLETED') {
                $activated = $this->activatedCardsService->fetchAndNormalize($woohooOrderId);

                if ($activated['success'] && ! empty($activated['cards'])) {
                    $this->woohooOrderService->storeCardDetailsEncrypted($order, $activated);
                    Log::info('Woohoo: Status COMPLETE — card details stored', ['order_id' => $order->id]);
                } else {
                    FetchActivatedCardsJob::dispatch($order, 0)->delay(now()->addSeconds(5));
                    Log::info('Woohoo COMPLETE: Activated Cards pending, FetchActivatedCardsJob dispatched', [
                        'order_id'    => $order->id,
                        'http_status' => $activated['http_status'] ?? null,
                    ]);
                }

                return 'complete';
            }

            return 'canceled';
        }

        return 'processing';
    }

    /**
     * Legacy tight-loop poll (e.g. tests). Prefer queued PollWoohooOrderStatusJob for production.
     *
     * @return array{status: string, card_details_stored: bool, attempts: int}
     */
    public function pollUntilComplete(Order $order, int $intervalSeconds = 10, int $maxAttempts = 30): array
    {
        $woohooOrderId = $order->woohoo_order_id;
        if (empty($woohooOrderId)) {
            return ['status' => 'UNKNOWN', 'card_details_stored' => false, 'attempts' => 0];
        }

        $attempts = 0;
        while ($attempts < $maxAttempts) {
            $attempts++;
            $data = $this->getOrderDetails($woohooOrderId);
            $outcome = $this->applyOrderDetailsSnapshot($order, $data);
            $order->refresh();

            if ($outcome === 'complete') {
                return [
                    'status'              => 'COMPLETE',
                    'card_details_stored' => ! empty($order->card_details_encrypted),
                    'attempts'            => $attempts,
                ];
            }
            if ($outcome === 'canceled' || $outcome === 'unavailable') {
                return [
                    'status'              => $outcome,
                    'card_details_stored' => false,
                    'attempts'            => $attempts,
                ];
            }

            sleep($intervalSeconds);
        }

        return [
            'status' => 'TIMEOUT',
            'card_details_stored' => false,
            'attempts' => $maxAttempts,
        ];
    }

    /**
     * Extract card details from Woohoo response. Order Details API returns cards as object with
     * "summary" (not actual card data). Order Create 201 returns cards as array of card objects.
     * Only store when we have actual card objects (with cardNumber, sku, etc.).
     *
     * @return array<int, array<string, mixed>>|null
     */
    protected function extractCardDetails(array $data): ?array
    {
        $raw = $data['cards'] ?? $data['cardDetails'] ?? $data['card_details'] ?? null;
        if (! is_array($raw) || empty($raw)) {
            return null;
        }
        // Order Details API: cards is {"summary": {...}} - not card objects, skip
        if (isset($raw['summary']) && ! isset($raw[0])) {
            return null;
        }
        // Must be list of card objects (have cardNumber, card_number, or sku)
        $items = isset($raw[0]) ? $raw : [$raw];
        $first = $items[0] ?? null;
        if (! is_array($first)) {
            return null;
        }
        $hasCardData = isset($first['cardNumber']) || isset($first['card_number']) || isset($first['sku']);
        if (! $hasCardData) {
            return null;
        }
        return array_values($raw);
    }

    /**
     * Map Woohoo Order Details API status to our delivery_status.
     * Doc statuses: PENDING, PROCESSING, CANCELED, COMPLETE.
     */
    protected function mapToDeliveryStatus(string $woohooStatus): string
    {
        return match (strtoupper($woohooStatus)) {
            'COMPLETE', 'COMPLETED', 'FULFILLED' => WoohooOrderService::DELIVERY_STATUS_FULFILLED,
            'CANCELED', 'CANCELLED', 'FAILED', 'ERROR' => WoohooOrderService::DELIVERY_STATUS_FAILED,
            default => WoohooOrderService::DELIVERY_STATUS_PENDING,
        };
    }

    /**
     * Map Woohoo status to order status. Only update to completed/cancelled when terminal.
     */
    protected function mapToOrderStatus(string $woohooStatus): string
    {
        return match (strtoupper($woohooStatus)) {
            'COMPLETE', 'COMPLETED', 'FULFILLED' => Order::STATUS_COMPLETED,
            'CANCELED', 'CANCELLED', 'FAILED', 'ERROR', 'NOT_AVAILABLE' => Order::STATUS_CANCELLED,
            default => Order::STATUS_PENDING,
        };
    }
}
