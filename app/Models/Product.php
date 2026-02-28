<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
        'is_featured',
        'loyalty_rate',
        'is_trending',
        'total_sales',
        'popularity_score',
        'views',
        'deal_price',
        'deal_start',
        'deal_end',
    ];

    protected function casts(): array
    {
        return [
            'min_price' => 'decimal:2',
            'max_price' => 'decimal:2',
            'deal_price' => 'decimal:2',
            'denominations' => 'array',
            'related_product_options' => 'array',
            'corporate_discounts' => 'array',
            'woohoo_attributes' => 'array',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'is_trending' => 'boolean',
            'deal_start' => 'datetime',
            'deal_end' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'product_tag')
            ->withTimestamps();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeTrending(Builder $query): Builder
    {
        return $query->where('is_trending', true);
    }

    public function scopeHotDeals(Builder $query): Builder
    {
        $now = now();
        return $query->whereNotNull('deal_price')
            ->whereNotNull('deal_start')
            ->whereNotNull('deal_end')
            ->where('deal_start', '<=', $now)
            ->where('deal_end', '>=', $now);
    }

    public function scopeBestSellers(Builder $query): Builder
    {
        return $query->where('total_sales', '>', 0)
            ->orderByDesc('total_sales');
    }

    public function scopeNewArrivals(Builder $query, int $days = 30): Builder
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function isOnDeal(): bool
    {
        if (!$this->deal_price || !$this->deal_start || !$this->deal_end) {
            return false;
        }
        $now = now();
        return $this->deal_start->lte($now) && $this->deal_end->gte($now);
    }
}
