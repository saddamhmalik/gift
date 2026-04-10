<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Crypt;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $item = $this->resource->relationLoaded('items') ? $this->resource->items->first() : null;

        // ── Decrypt stored card data ───────────────────────────────────────
        $storedCards = null;   // the `cards` array (backward-compat)
        $cardProducts = null;   // Woohoo `products` map (keyed by SKU)
        $cardCurrency = null;   // Woohoo currency object
        $deliveryMode = null;   // Woohoo deliveryMode
        $cardDelivery = null;   // Woohoo delivery summary
        $totalCards = null;   // total_cards

        if ($this->card_details_encrypted) {
            try {
                $raw = Crypt::decryptString($this->card_details_encrypted);
                $decoded = json_decode($raw, true);

                if (is_array($decoded)) {
                    if (isset($decoded['cards'])) {
                        // ── New format: full Activated Cards API response ──────
                        $storedCards = $decoded['cards'] ?? null;
                        $cardProducts = $decoded['products'] ?? null;
                        $cardCurrency = $decoded['currency'] ?? null;
                        $deliveryMode = $decoded['deliveryMode'] ?? null;
                        $cardDelivery = $decoded['delivery'] ?? null;
                        $totalCards = $decoded['total_cards'] ?? null;
                    } else {
                        // ── Legacy format: plain array of card objects ─────────
                        $storedCards = $decoded;
                    }
                }
            } catch (\Throwable) {
                // Decryption failure — leave everything null
            }
        }

        $pendingLoyaltyEstimate = $this->resource->pendingLoyaltyEstimate();

        return [
            'id' => $this->id,
            'order_token' => $this->order_token,
            'status' => $this->status,
            'total_amount' => $this->total_amount,
            'points_used' => $this->points_used,
            'points_earned' => $this->points_earned,
            'loyalty_credit_delay_hours' => (int) config('loyalty.credit_delay_hours', 24),
            'loyalty_points_pending' => $pendingLoyaltyEstimate !== null,
            'loyalty_points_estimate' => $pendingLoyaltyEstimate,
            'currency_code' => $this->currency_code,
            'item' => $item ? new OrderItemResource($item) : null,
            'woohoo_refno' => $this->woohoo_refno,
            'woohoo_order_id' => $this->woohoo_order_id,
            'delivery_status' => $this->delivery_status,

            // Refund info
            'refund_status' => $this->refund_status,
            'refund_reason' => $this->refund_reason,
            'refunded_at' => $this->refunded_at?->toIso8601String(),

            // Card data — card_details is the cards array for backward compat
            'card_details' => $storedCards,
            'card_products' => $cardProducts,
            'card_currency' => $cardCurrency,
            'delivery_mode' => $deliveryMode,
            'card_delivery' => $cardDelivery,
            'total_cards' => $totalCards,

            // Gift / delivery metadata
            'order_mode' => $this->order_mode ?? 'SELF',
            'woohoo_delivery_mode' => $this->delivery_mode ?? 'API',
            'gift_recipient_name' => $this->gift_recipient_name,
            'gift_recipient_email' => $this->gift_recipient_email,
            'gift_recipient_phone' => $this->gift_recipient_phone,
            'gift_message' => $this->gift_message,

            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
