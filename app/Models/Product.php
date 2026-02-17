<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'external_id',
        'name',
        'slug',
        'url',
        'description',
        'offer_short_desc',
        'currency_code',
        'min_price',
        'max_price',
        'denominations',
        'price_type',
        'image_url',
        'thumbnail_url',
        'related_product_options',
        'corporate_discounts',
        'product_type',
        'purchaser_limit',
        'purchaser_description',
        'tnc_link',
        'tnc_content',
        'woohoo_attributes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'min_price' => 'decimal:2',
            'max_price' => 'decimal:2',
            'denominations' => 'array',
            'related_product_options' => 'array',
            'corporate_discounts' => 'array',
            'woohoo_attributes' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
