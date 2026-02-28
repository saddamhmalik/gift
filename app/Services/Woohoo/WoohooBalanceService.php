<?php

namespace App\Services\Woohoo;

use App\Services\WoohooClient;
use Illuminate\Support\Facades\Log;

class WoohooBalanceService
{
    public function __construct(protected WoohooClient $client) {}

    /**
     * Check the balance of a gift card.
     *
     * @param  string       $cardNumber  Gift card number (required)
     * @param  string|null  $pin         Gift card PIN (optional)
     * @param  string|null  $sku         Product SKU (optional — do NOT pass if blank)
     * @return array{
     *   success: bool,
     *   balance?: string,
     *   cardNumber?: string,
     *   expiry?: string,
     *   status?: string,
     *   currency?: array,
     *   error?: string,
     *   code?: string|int,
     *   http_status: int,
     * }
     */
    public function check(string $cardNumber, ?string $pin = null, ?string $sku = null): array
    {
        $payload = ['cardNumber' => trim($cardNumber)];

        if ($pin !== null && trim($pin) !== '') {
            $payload['pin'] = trim($pin);
        }
        // Woohoo says: "If you do not have sku, do not pass this parameter itself"
        if ($sku !== null && trim($sku) !== '') {
            $payload['sku'] = trim($sku);
        }

        $response   = $this->client->post('/rest/v3/balance', $payload);
        $httpStatus = $response->status();
        $body       = $response->json() ?? [];

        Log::info('Woohoo Balance API', [
            'cardNumber' => $payload['cardNumber'],
            'http'       => $httpStatus,
            'response'   => $body,
        ]);

        // 200 or 201 = success (docs say 201 but sandbox returns 200)
        if ($httpStatus === 200 || $httpStatus === 201) {
            return [
                'success'    => true,
                'cardNumber' => $body['cardNumber'] ?? $payload['cardNumber'],
                'balance'    => $body['balance']    ?? null,
                'expiry'     => $body['expiry']     ?? null,
                'status'     => $body['status']     ?? null,
                'currency'   => $body['currency']   ?? null,
                'http_status'=> $httpStatus,
            ];
        }

        // 400 / other error
        return [
            'success'     => false,
            'error'       => $body['message'] ?? 'Balance enquiry failed. Please check the card details and try again.',
            'code'        => $body['code']    ?? null,
            'http_status' => $httpStatus,
        ];
    }
}
