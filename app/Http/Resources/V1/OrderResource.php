<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $item = $this->resource->relationLoaded('items') ? $this->resource->items->first() : null;

        return [
            'id' => $this->id,
            'order_token' => $this->order_token,
            'status' => $this->status,
            'total_amount' => $this->total_amount,
            'currency_code' => $this->currency_code,
            'item' => $item ? new OrderItemResource($item) : null,
            'woohoo_refno' => $this->woohoo_refno,
            'woohoo_order_id' => $this->woohoo_order_id,
            'delivery_status' => $this->delivery_status,
        ];
    }
}
