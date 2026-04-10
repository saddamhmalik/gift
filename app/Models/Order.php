<?php

namespace App\Models;

use App\Services\Loyalty\LoyaltyService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    public const REFUND_STATUS_PENDING = 'pending';

    public const REFUND_STATUS_REFUNDED = 'refunded';

    public const REFUND_STATUS_FAILED = 'failed';

    protected $fillable = [
        'user_id',
        'order_token',
        'payment_txnid',
        'payu_mihpayid',
        'payu_paid_amount',
        'refund_status',
        'refund_reason',
        'refunded_at',
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
            'total_amount' => 'decimal:2',
            'points_used' => 'decimal:2',
            'points_earned' => 'decimal:2',
            'payu_paid_amount' => 'decimal:2',
            'refunded_at' => 'datetime',
            'woohoo_sync' => 'boolean',
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

    public const DELIVERY_API = 'API';

    public const DELIVERY_EMAIL = 'EMAIL';

    public const DELIVERY_SMS = 'SMS';

    public const DELIVERY_ANY = 'ANY';

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

    /**
     * Estimated PayFlex points for the cash portion if credit is still pending; null otherwise.
     * Single source for API `loyalty_points_pending` / `loyalty_points_estimate`.
     */
    public function pendingLoyaltyEstimate(): ?float
    {
        if ($this->status !== self::STATUS_COMPLETED) {
            return null;
        }

        if (in_array($this->refund_status, [self::REFUND_STATUS_PENDING, self::REFUND_STATUS_REFUNDED], true)) {
            return null;
        }

        if ((float) $this->points_earned > 0) {
            return null;
        }

        if (LoyaltyPoint::query()
            ->where('order_id', $this->id)
            ->where('type', LoyaltyPoint::TYPE_CREDIT)
            ->exists()) {
            return null;
        }

        $estimate = app(LoyaltyService::class)->estimateEarnedPointsForOrder($this);

        return $estimate > 0 ? $estimate : null;
    }

    public function isLoyaltyCreditPending(): bool
    {
        return $this->pendingLoyaltyEstimate() !== null;
    }
}
