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
            'url'                  => $this->url,
            'price_type'           => $this->price_type,
            'denominations'        => $this->denominations ?? [],
            'product_type'         => $this->product_type,
            'purchaser_limit'      => $this->purchaser_limit,
            'purchaser_description'=> $this->purchaser_description,
            'tnc_content'          => $this->tnc_content,
        ]);
    }
}
