<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoyaltyPoint extends Model
{
    protected $fillable = [
        'user_id',
        'order_id',
        'type',
        'points',
        'description',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'points'     => 'decimal:2',
            'expires_at' => 'datetime',
        ];
    }

    public const TYPE_CREDIT = 'credit';
    public const TYPE_DEBIT  = 'debit';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function scopeActive($query)
    {
        return $query->where('type', self::TYPE_CREDIT)
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()));
    }

    public function scopeExpired($query)
    {
        return $query->where('type', self::TYPE_CREDIT)
            ->where('expires_at', '<=', now());
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->type === self::TYPE_CREDIT
            && $this->expires_at !== null
            && $this->expires_at->isPast();
    }
}
