<?php

namespace App\Services\Woohoo;

use App\Exceptions\Woohoo\WoohooOrderException;
use App\Models\Order;
use App\Services\WoohooClient;
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
        protected WoohooOrderPayloadBuilder $payloadBuilder
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

        $path = config('woohoo.endpoints.orders', '/rest/v3/orders');
        $response = $this->client->post($path, $payload);

        $status = $response->status();
        $body = $response->json() ?? [];

        if (! $response->successful()) {
            $this->handleErrorResponse($status, $body, $payload);
        }

        $woohooOrderId = $body['orderId'] ?? $body['order_id'] ?? null;
        $order->update([
            'woohoo_refno' => $refno,
            'woohoo_order_id' => $woohooOrderId,
            'woohoo_sync' => $syncOnly,
            'woohoo_request' => $payload,
            'woohoo_response' => $body,
            'billing_email' => $billing['email'] ?? null,
            'billing_telephone' => $billing['telephone'] ?? null,
            'billing_name' => $billing['name'] ?? null,
            'address' => $address ?: null,
            'delivery_status' => $status === 201 ? self::DELIVERY_STATUS_FULFILLED : self::DELIVERY_STATUS_PENDING,
            'status' => $status === 201 ? Order::STATUS_COMPLETED : Order::STATUS_PENDING,
        ]);

        if ($status === 201 && ! empty($body['cardDetails'] ?? $body['card_details'] ?? null)) {
            $cardDetails = $body['cardDetails'] ?? $body['card_details'];
            $this->storeCardDetailsEncrypted($order, $cardDetails);
        }

        return [
            'status' => $status,
            'refno' => $refno,
            'woohoo_order_id' => $woohooOrderId,
            'card_details' => $status === 201 ? ($body['cardDetails'] ?? $body['card_details'] ?? null) : null,
            'sync' => $syncOnly,
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
