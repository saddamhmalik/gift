<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Voucher extends Model
{
    protected $fillable = [
        'external_id',
        'brand_id',
        'name',
        'slug',
        'description',
        'min_value',
        'max_value',
        'denominations',
        'image_url',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'denominations' => 'array',
            'min_value' => 'decimal:2',
            'max_value' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
