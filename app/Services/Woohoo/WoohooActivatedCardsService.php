<?php

namespace App\Services\Woohoo;

use App\Services\WoohooClient;
use Illuminate\Support\Facades\Log;

/**
 * Wraps the Woohoo Activated Cards API: GET /rest/v3/order/{id}/cards/
 *
 * Use this to retrieve the full, canonical card details for any completed order —
 * both for synchronous (201) and asynchronous (202 → COMPLETE) orders.
 * It avoids partners needing to store sensitive card data themselves.
 */
class WoohooActivatedCardsService
{
    /** Woohoo HTTP status codes for the Activated Cards endpoint */
    public const STATUS_OK         = 200;
    public const STATUS_PROCESSING = 409; // Still activating — retry later
    public const STATUS_CANCELLED  = 410; // Order cancelled

    public function __construct(
        protected WoohooClient $client
    ) {}

    /**
     * Fetch one page of activated cards.
     *
     * @return array{
     *   success: bool,
     *   http_status: int,
     *   state: string|null,
     *   cards: list<array>,
     *   products: array<string, array>,
     *   currency: array,
     *   deliveryMode: string|null,
     *   delivery: array,
     *   total_cards: int,
     *   raw: array
     * }
     */
    public function fetchCards(string $woohooOrderId, int $offset = 0, int $limit = 100): array
    {
        $path     = "/rest/v3/order/{$woohooOrderId}/cards/";
        $response = $this->client->get($path, ['offset' => $offset, 'limit' => $limit]);
        $status   = $response->status();
        $body     = $response->json() ?? [];

        if ($status === self::STATUS_OK) {
            return [
                'success'      => true,
                'http_status'  => $status,
                'state'        => null,
                'cards'        => array_values($body['cards']  ?? []),
                'products'     => $body['products']     ?? [],
                'currency'     => $body['currency']     ?? [],
                'deliveryMode' => $body['deliveryMode'] ?? null,
                'delivery'     => $body['delivery']     ?? [],
                'total_cards'  => (int) ($body['total_cards'] ?? 0),
                'raw'          => $body,
            ];
        }

        // 409 → still processing; 410 → cancelled; 400/500 → error
        $logLevel = in_array($status, [self::STATUS_PROCESSING, self::STATUS_CANCELLED], true)
            ? 'info'
            : 'warning';

        Log::$logLevel('Woohoo Activated Cards API non-200', [
            'woohoo_order_id' => $woohooOrderId,
            'http_status'     => $status,
            'body'            => $body,
        ]);

        return [
            'success'      => false,
            'http_status'  => $status,
            'state'        => $body['state'] ?? null,  // PROCESSING | CANCELED
            'message'      => $body['message'] ?? null,
            'cards'        => [],
            'products'     => [],
            'currency'     => [],
            'deliveryMode' => null,
            'delivery'     => [],
            'total_cards'  => 0,
            'raw'          => $body,
        ];
    }

    /**
     * Fetch ALL cards for an order, iterating through pages automatically.
     */
    public function fetchAllCards(string $woohooOrderId, int $limit = 100): array
    {
        $first = $this->fetchCards($woohooOrderId, 0, $limit);
        if (! $first['success']) {
            return $first;
        }

        $total = $first['total_cards'];
        $all   = $first['cards'];

        $offset = $limit;
        while ($offset < $total) {
            $page = $this->fetchCards($woohooOrderId, $offset, $limit);
            if (! $page['success']) {
                break;
            }
            $all    = array_merge($all, $page['cards']);
            $offset += $limit;
        }

        return array_merge($first, ['cards' => $all]);
    }

    /**
     * Fetch all cards and merge product-level metadata (balanceEnquiryInstruction,
     * specialInstruction, cardBehaviour, productImages) into each card object so
     * the frontend only needs to work with a single flat card array.
     *
     * @return array Same shape as fetchAllCards(); `cards` items are enriched.
     */
    public function fetchAndNormalize(string $woohooOrderId): array
    {
        $result = $this->fetchAllCards($woohooOrderId);
        if (! $result['success']) {
            return $result;
        }

        $products = $result['products']; // keyed by SKU

        $normalized = array_map(function (array $card) use ($products): array {
            $sku  = $card['sku'] ?? null;
            $prod = $sku ? ($products[$sku] ?? []) : [];

            // Merge product-level fields only when not already on the card
            return array_merge($card, [
                'balanceEnquiryInstruction' => $card['balanceEnquiryInstruction']
                    ?? $prod['balanceEnquiryInstruction']
                    ?? null,
                'specialInstruction'        => $card['specialInstruction']
                    ?? $prod['specialInstruction']
                    ?? null,
                'cardBehaviour'             => $card['cardBehaviour']
                    ?? $prod['cardBehaviour']
                    ?? null,
                'productImages'             => $prod['images'] ?? null,
            ]);
        }, $result['cards']);

        return array_merge($result, ['cards' => $normalized]);
    }
}
