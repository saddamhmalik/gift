<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = [
        'external_id',
        'parent_id',
        'name',
        'slug',
        'url',
        'description',
        'short_description',
        'canonical_url',
        'image_url',
        'thumbnail_url',
        'color_code',
        'bg_color_code',
        'offer_description',
        'meta_index',
        'meta_keyword',
        'page_title',
        'meta_description',
        'sub_category_filter',
        'subcategories_count',
        'sort_order',
        'is_active',
        'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sub_category_filter' => 'boolean',
            'meta_index' => 'boolean',
            'last_synced_at' => 'datetime',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function brands(): HasMany
    {
        return $this->hasMany(Brand::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
