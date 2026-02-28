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
        'payment_txnid',
        'status',
        'total_amount',
        'points_used',
        'points_earned',
        'currency_code',
        'order_mode',
        'delivery_mode',
        'gift_recipient_name',
        'gift_recipient_email',
        'gift_recipient_phone',
        'gift_message',
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
            'total_amount'  => 'decimal:2',
            'points_used'   => 'decimal:2',
            'points_earned' => 'decimal:2',
            'woohoo_sync'   => 'boolean',
            'address' => 'array',
            'woohoo_request' => 'array',
            'woohoo_response' => 'array',
        ];
    }

    public const STATUS_PENDING = 'pending';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public const MODE_SELF = 'SELF';
    public const MODE_GIFT = 'GIFT';

    public const DELIVERY_API   = 'API';
    public const DELIVERY_EMAIL = 'EMAIL';
    public const DELIVERY_SMS   = 'SMS';
    public const DELIVERY_ANY   = 'ANY';

    public function isGift(): bool
    {
        return $this->order_mode === self::MODE_GIFT;
    }

    public function isApiDelivery(): bool
    {
        return ($this->delivery_mode ?? 'API') === self::DELIVERY_API;
    }

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
