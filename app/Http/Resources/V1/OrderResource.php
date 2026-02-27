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

        $cardDetails = null;
        if ($this->card_details_encrypted) {
            try {
                $cardDetails = Crypt::decryptString($this->card_details_encrypted);
                $cardDetails = json_decode($cardDetails, true);
            } catch (\Throwable) {
                $cardDetails = null;
            }
        }

        return [
            'id'              => $this->id,
            'order_token'     => $this->order_token,
            'status'          => $this->status,
            'total_amount'    => $this->total_amount,
            'currency_code'   => $this->currency_code,
            'item'            => $item ? new OrderItemResource($item) : null,
            'woohoo_refno'    => $this->woohoo_refno,
            'woohoo_order_id' => $this->woohoo_order_id,
            'delivery_status' => $this->delivery_status,
            'card_details'    => $cardDetails,
            'created_at'      => $this->created_at?->toIso8601String(),
        ];
    }
}
