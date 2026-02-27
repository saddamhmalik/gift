<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'name'               => $this->name,
            'slug'               => $this->slug,
            'description'        => $this->description,
            'short_description'  => $this->short_description,
            'image_url'          => $this->image_url,
            'thumbnail_url'      => $this->thumbnail_url,
            'color_code'         => $this->color_code,
            'bg_color_code'      => $this->bg_color_code,
            'subcategories_count'=> $this->whenLoaded('children', fn () => $this->children->count()),
            'subcategories'      => CategoryResource::collection($this->whenLoaded('children')),
            'products'           => ProductResource::collection($this->whenLoaded('products')),
        ];
    }
}
