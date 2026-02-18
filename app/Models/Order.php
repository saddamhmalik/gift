<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'order_token',
        'status',
        'total_amount',
        'currency_code',
        'woohoo_refno',
        'woohoo_order_id',
        'woohoo_sync',
        'card_details_encrypted',
        'delivery_status',
        'billing_email',
        'billing_telephone',
        'billing_name',
        'address',
        'woohoo_request',
        'woohoo_response',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'woohoo_sync' => 'boolean',
            'address' => 'array',
            'woohoo_request' => 'array',
            'woohoo_response' => 'array',
        ];
    }

    public const STATUS_PENDING = 'pending';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }
}
