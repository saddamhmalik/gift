<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $base = (new ProductResource($this->resource))->toArray($request);
        return array_merge($base, [
            'url' => $this->url,
            'denominations' => $this->denominations,
            'related_product_options' => $this->related_product_options,
            'corporate_discounts' => $this->corporate_discounts,
            'product_type' => $this->product_type,
            'purchaser_limit' => $this->purchaser_limit,
            'purchaser_description' => $this->purchaser_description,
            'tnc_link' => $this->tnc_link,
            'tnc_content' => $this->tnc_content,
            'woohoo_attributes' => $this->woohoo_attributes,
        ]);
    }
}
