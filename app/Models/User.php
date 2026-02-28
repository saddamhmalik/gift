<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'email',
        'password',
        'phone',
        'google_id',
        'avatar',
        'otp',
        'otp_expires_at',
        'phone_verified_at',
        'pending_email',
        'email_change_token',
        'email_change_expires_at',
        'pending_phone',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'otp',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function loyaltyPoints(): HasMany
    {
        return $this->hasMany(LoyaltyPoint::class);
    }

    public function loyaltyBalance(): float
    {
        $earned = (float) $this->loyaltyPoints()
            ->where('type', LoyaltyPoint::TYPE_CREDIT)
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->sum('points');

        $spent = (float) $this->loyaltyPoints()
            ->where('type', LoyaltyPoint::TYPE_DEBIT)
            ->sum('points');

        return max(0, $earned - $spent);
    }

    public function getFullNameAttribute(): string
    {
        return trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? '')) ?: ($this->name ?? 'User');
    }

    protected function casts(): array
    {
        return [
            'email_verified_at'       => 'datetime',
            'otp_expires_at'          => 'datetime',
            'phone_verified_at'       => 'datetime',
            'email_change_expires_at' => 'datetime',
            'password'                => 'hashed',
        ];
    }
}
