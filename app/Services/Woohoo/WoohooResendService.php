<?php

namespace App\Services\Woohoo;

use App\Models\Order;
use App\Services\WoohooClient;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class WoohooResendService
{
    public function __construct(protected WoohooClient $client) {}

    /**
     * Resend card details to the recipient (or override with new contact info).
     *
     * @param  array{name?:string, email?:string, telephone?:string}  $overrides
     * @return array{success: bool, message?: string, error?: string, status?: int}
     */
    public function resend(Order $order, array $overrides = []): array
    {
        if (! $order->woohoo_order_id) {
            throw new InvalidArgumentException('Order has no Woohoo order ID. Cannot resend.');
        }

        $deliveryMode = $order->delivery_mode ?? 'API';
        if ($deliveryMode === 'API') {
            throw new InvalidArgumentException('Resend is only available for EMAIL, SMS, or ANY delivery orders.');
        }

        // Build the resend payload
        $payload = $this->buildPayload($order, $overrides);

        $path     = "/rest/v3/orders/{$order->woohoo_order_id}/resend";
        $response = $this->client->post($path, $payload);

        $status = $response->status();
        $body   = $response->json();

        Log::info('Woohoo Resend API response', [
            'order_id'        => $order->id,
            'woohoo_order_id' => $order->woohoo_order_id,
            'status'          => $status,
            'body'            => $body,
        ]);

        if ($status === 200 || $status === 202) {
            return [
                'success' => true,
                'message' => $body['message'] ?? 'Card details resent successfully.',
                'status'  => $status,
            ];
        }

        $errorMsg = $body['message'] ?? $body['error'] ?? 'Failed to resend card details.';
        return [
            'success' => false,
            'error'   => $errorMsg,
            'status'  => $status,
        ];
    }

    /**
     * Build payload. Uses per-card format when card IDs are available,
     * otherwise uses the consolidated (single-recipient) format.
     */
    protected function buildPayload(Order $order, array $overrides): array
    {
        $cardIds = $this->extractCardIds($order);

        if (! empty($cardIds)) {
            // Per-card format — each card gets the same recipient overrides
            $cards = [];
            foreach ($cardIds as $cardId) {
                $entry = ['id' => $cardId];

                $name = $overrides['name'] ?? $order->gift_recipient_name;
                if ($name) {
                    $entry['name'] = $name;
                }
                $email = $overrides['email'] ?? $order->gift_recipient_email;
                if ($email) {
                    $entry['email'] = $email;
                }
                $telephone = $this->toE164($overrides['telephone'] ?? $order->gift_recipient_phone ?? '');
                if ($telephone) {
                    $entry['telephone'] = $telephone;
                }

                $cards[] = $entry;
            }
            return ['cards' => $cards];
        }

        // Consolidated format (no card IDs stored yet)
        $payload = [];

        $name = $overrides['name'] ?? $order->gift_recipient_name;
        if ($name) {
            $parts              = explode(' ', trim($name), 2);
            $payload['firstname'] = $parts[0];
            if (isset($parts[1]) && $parts[1] !== '') {
                $payload['lastname'] = $parts[1];
            }
        }

        $email = $overrides['email'] ?? $order->gift_recipient_email;
        if ($email) {
            $payload['email'] = $email;
        }

        $telephone = $this->toE164($overrides['telephone'] ?? $order->gift_recipient_phone ?? '');
        if ($telephone) {
            $payload['telephone'] = $telephone;
        }

        // Woohoo requires at least one field — ensure we always send something
        if (empty($payload)) {
            // Fall back to the buyer's email from order
            $order->loadMissing('user');
            $fallbackEmail = $order->billing_email ?? $order->user?->email;
            if ($fallbackEmail) {
                $payload['email'] = $fallbackEmail;
            } else {
                throw new InvalidArgumentException('No recipient contact information found to resend to.');
            }
        }

        return $payload;
    }

    /**
     * Extract Woohoo cardId values from the encrypted card details stored on the order.
     *
     * @return int[]
     */
    protected function extractCardIds(Order $order): array
    {
        if (! $order->card_details_encrypted) {
            return [];
        }

        try {
            $raw     = Crypt::decryptString($order->card_details_encrypted);
            $decoded = json_decode($raw, true);

            $cards = $decoded['cards'] ?? (array_values(array_filter(
                $decoded ?? [],
                fn ($v) => is_array($v) && isset($v['cardId'])
            )));

            return array_values(array_filter(
                array_map(fn ($c) => is_array($c) ? (int) ($c['cardId'] ?? 0) : 0, $cards),
                fn ($id) => $id > 0
            ));
        } catch (\Throwable) {
            return [];
        }
    }

    /** Normalise phone number to E164 format. */
    protected function toE164(string $phone): string
    {
        $phone = trim($phone);
        if ($phone === '') {
            return '';
        }
        if (preg_match('/^\+[1-9]\d{6,14}$/', $phone)) {
            return $phone;
        }
        $digits = preg_replace('/\D/', '', $phone);
        if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
            return '+' . $digits;
        }
        if (strlen($digits) === 10) {
            return '+91' . $digits;
        }
        if (strlen($digits) >= 11) {
            return '+' . $digits;
        }
        return '';
    }
}
