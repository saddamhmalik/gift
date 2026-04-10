<?php

namespace App\Services\Woohoo;

use App\Exceptions\Woohoo\WoohooOrderException;
use App\Jobs\PollWoohooOrderStatusJob;
use App\Jobs\WoohooOrderPostTimeoutJob;
use App\Models\Order;
use App\Services\WoohooClient;
use App\Services\Woohoo\WoohooActivatedCardsService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class WoohooOrderService
{
    public const DELIVERY_STATUS_PENDING = 'pending';
    public const DELIVERY_STATUS_FULFILLED = 'fulfilled';
    public const DELIVERY_STATUS_FAILED = 'failed';

    public function __construct(
        protected WoohooClient $client,
        protected WoohooRefnoGenerator $refnoGenerator,
        protected WoohooOrderPayloadBuilder $payloadBuilder,
        protected WoohooActivatedCardsService $activatedCardsService,
    ) {}

    /**
     * Create Woohoo order (SVC). Generates refno, builds payload, sends POST /rest/v3/orders.
     * On 201: stores card details encrypted and returns response.
     * On 202: returns response and caller should poll Order Status API.
     *
     * @param  array{email: string, telephone: string, name?: string, line1?: string, city?: string, state?: string, postalCode?: string, country?: string}  $billing
     * @param  array<string, string>  $address
     * @return array{status: int, refno: string, woohoo_order_id?: string, card_details?: array, sync: bool}
     */
    public function createOrder(Order $order, array $billing, array $address = [], bool $syncOnly = false): array
    {
        $refno = $this->refnoGenerator->generate();
        $payload = $this->payloadBuilder->build($order, $refno, $billing, $address, $syncOnly);

        // Persist refno, sync flag and request payload before the API call so this
        // information is always available regardless of whether Woohoo succeeds or fails.
        $order->update([
            'woohoo_refno'   => $refno,
            'woohoo_sync'    => $syncOnly,
            'woohoo_request' => $payload,
            'billing_email'     => $billing['email'] ?? null,
            'billing_telephone' => $billing['telephone'] ?? null,
            'billing_name'      => $billing['name'] ?? null,
            'address'           => $address ?: null,
        ]);

        $path = config('woohoo.endpoints.orders', '/rest/v3/orders');
        try {
            $response = $this->client->post($path, $payload);
        } catch (ConnectionException $e) {
            // refno, woohoo_sync and woohoo_request are already persisted above
            $order->update([
                'woohoo_response' => [
                    'client_timeout' => true,
                    'timeout_at'     => now()->toIso8601String(),
                    'message'        => $e->getMessage(),
                ],
                'delivery_status' => self::DELIVERY_STATUS_PENDING,
                'status'          => Order::STATUS_PENDING,
            ]);
            $delaySec = (int) config('woohoo.order_timeout_status_delay_sec', 40);
            WoohooOrderPostTimeoutJob::dispatch($order)->delay(now()->addSeconds($delaySec));
            Log::error('Woohoo Order API client timeout', ['order_id' => $order->id, 'refno' => $refno]);
            throw WoohooOrderException::clientTimeout($refno, $e);
        }

        $status = $response->status();
        $body = $response->json() ?? [];

        if (! $response->successful()) {
            $this->handleErrorResponse($status, $body, $payload);
        }

        $woohooOrderId = $body['orderId'] ?? $body['order_id'] ?? null;
        $order->update([
            'woohoo_order_id' => $woohooOrderId,
            'woohoo_response' => $body,
            'delivery_status' => $status === 201 ? self::DELIVERY_STATUS_FULFILLED : self::DELIVERY_STATUS_PENDING,
            'status'          => $status === 201 ? Order::STATUS_COMPLETED : Order::STATUS_PENDING,
        ]);

        // ── 201 (syncOnly=true): Woohoo returns cards directly in the create response body.
        // Save them immediately — no extra Activated Cards API call needed.
        // Per Woohoo docs: "GC details will be returned in the response" for syncOnly=true.
        // Only fall back to Activated Cards API if `cards` is absent from the body.
        if ($status === 201 && $woohooOrderId) {
            $cardsFromBody = isset($body['cards']) && is_array($body['cards']) ? array_values($body['cards']) : [];

            if (! empty($cardsFromBody)) {
                $storageData = $this->buildStorageDataFromCreateResponse($body, $cardsFromBody);
                $this->storeCardDetailsEncrypted($order, $storageData);
                Log::info('Woohoo 201: cards stored from create response', [
                    'order_id'   => $order->id,
                    'card_count' => count($cardsFromBody),
                ]);
            } else {
                // Cards absent in create response — fall back to Activated Cards API
                $activated = $this->activatedCardsService->fetchAndNormalize($woohooOrderId);

                if ($activated['success'] && ! empty($activated['cards'])) {
                    $this->storeCardDetailsEncrypted($order, $activated);
                    Log::info('Woohoo 201: cards stored via Activated Cards API', ['order_id' => $order->id]);
                } elseif ($activated['http_status'] === WoohooActivatedCardsService::STATUS_PROCESSING) {
                    PollWoohooOrderStatusJob::dispatchForCardSyncDelay($order);
                    Log::info('Woohoo 201: Activated Cards still processing — Status API poll queued', ['order_id' => $order->id]);
                } else {
                    PollWoohooOrderStatusJob::dispatchForCardSyncDelay($order);
                    Log::warning('Woohoo 201: Activated Cards unavailable — Status API poll queued', [
                        'order_id'    => $order->id,
                        'http_status' => $activated['http_status'],
                    ]);
                }
            }
        }

        return [
            'status'          => $status,
            'refno'           => $refno,
            'woohoo_order_id' => $woohooOrderId,
            'sync'            => $syncOnly,
        ];
    }

    /**
     * Build a storage-ready array from a 201 create response body.
     * Normalises to the same shape as WoohooActivatedCardsService::fetchAndNormalize()
     * so the rest of the app (OrderResource, frontend) can handle it identically.
     *
     * @param  array<string, mixed>   $body
     * @param  list<array<string, mixed>>  $cards
     * @return array<string, mixed>
     */
    protected function buildStorageDataFromCreateResponse(array $body, array $cards): array
    {
        $products = is_array($body['products'] ?? null) ? $body['products'] : [];

        // Merge product-level metadata into each card (mirrors fetchAndNormalize behaviour)
        $normalized = array_map(function (array $card) use ($products): array {
            $sku  = $card['sku'] ?? null;
            $prod = $sku ? ($products[$sku] ?? []) : [];

            return array_merge($card, [
                'balanceEnquiryInstruction' => $card['balanceEnquiryInstruction']
                    ?? $prod['balanceEnquiryInstruction']
                    ?? null,
                'specialInstruction' => $card['specialInstruction']
                    ?? $prod['specialInstruction']
                    ?? null,
                'cardBehaviour' => $card['cardBehaviour']
                    ?? $prod['cardBehaviour']
                    ?? null,
                'productImages' => $prod['images'] ?? null,
            ]);
        }, $cards);

        return [
            'success'      => true,
            'cards'        => $normalized,
            'products'     => $products,
            'currency'     => is_array($body['currency'] ?? null) ? $body['currency'] : [],
            'deliveryMode' => $body['deliveryMode'] ?? null,
            'delivery'     => [],
            'total_cards'  => count($normalized),
        ];
    }

    /**
     * Store card details encrypted on the order.
     *
     * @param  array<string, mixed>  $cardDetails
     */
    public function storeCardDetailsEncrypted(Order $order, array $cardDetails): void
    {
        try {
            $encrypted = Crypt::encryptString(json_encode($cardDetails));
            $order->update(['card_details_encrypted' => $encrypted]);
        } catch (\Throwable $e) {
            Log::error('Woohoo: failed to encrypt/store card details', [
                'order_id' => $order->id,
                'message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Decrypt and return card details for an order (if stored).
     *
     * @return array<string, mixed>|null
     */
    public function getCardDetailsDecrypted(Order $order): ?array
    {
        $enc = $order->card_details_encrypted;
        if (empty($enc)) {
            return null;
        }
        try {
            $json = Crypt::decryptString($enc);
            $decoded = json_decode($json, true);
            return is_array($decoded) ? $decoded : null;
        } catch (\Throwable $e) {
            Log::warning('Woohoo: failed to decrypt card details', ['order_id' => $order->id]);
            return null;
        }
    }

    /**
     * @param  array<string, mixed>  $body
     * @param  array<string, mixed>  $payload
     * @throws WoohooOrderException
     */
    protected function handleErrorResponse(int $status, array $body, array $payload): void
    {
        Log::error('Woohoo Order Create failed', [
            'http_status' => $status,
            'woohoo_response' => $body,
            'payload_refno' => $payload['refno'] ?? null,
        ]);
        $code = (string) ($body['errorCode'] ?? $body['code'] ?? '');
        $message = $body['message'] ?? $body['errorMessage'] ?? 'Unknown Woohoo error';

        if ($code === '5313') {
            throw WoohooOrderException::duplicateRefno($body);
        }
        if ($code === '6063') {
            throw WoohooOrderException::insufficientBalance($body);
        }
        if ($code === '5035') {
            throw WoohooOrderException::svcNotEnabled($body);
        }

        throw WoohooOrderException::fromResponse($body, "Woohoo order failed: {$message}");
    }
}
