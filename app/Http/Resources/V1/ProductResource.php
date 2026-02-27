<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'offer_short_desc' => $this->offer_short_desc,
            'currency_code' => $this->currency_code,
            'min_price' => $this->min_price,
            'max_price' => $this->max_price,
            'image_url' => $this->image_url,
            'thumbnail_url' => $this->thumbnail_url,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'price_type'   => $this->price_type,
            'denominations'=> $this->denominations ?? [],
            'is_featured' => $this->is_featured ?? false,
            'is_trending' => $this->is_trending ?? false,
            'views' => $this->views ?? 0,
            'deal_price' => $this->deal_price,
            'deal_start' => $this->deal_start?->toIso8601String(),
            'deal_end' => $this->deal_end?->toIso8601String(),
            'is_on_deal' => $this->resource->isOnDeal(),
        ];
    }
}
