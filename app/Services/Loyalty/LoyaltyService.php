<?php

namespace App\Services\Loyalty;

use App\Models\LoyaltyPoint;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LoyaltyService
{
    /**
     * Estimated earn points for the cash portion (same rules as {@see creditForOrder}).
     * Used for UI before the delayed credit job runs.
     */
    public function estimateEarnedPointsForOrder(Order $order): float
    {
        $order->loadMissing(['items.product']);

        $amountPaid = max(0, (float) $order->total_amount - (float) $order->points_used);
        if ($amountPaid <= 0) {
            return 0.0;
        }

        $rate = $this->resolveRate($order);

        return max(0.0, round($amountPaid * $rate, 2));
    }

    /**
     * Credit points for the cash portion of an order (not points-paid portion).
     * Rate comes from the first line item's product or global default.
     * In production this is invoked from {@see \App\Jobs\CreditLoyaltyForOrderJob} after the configured delay.
     *
     * Uses a row lock so concurrent workers cannot double-credit.
     */
    public function creditForOrder(Order $order): ?LoyaltyPoint
    {
        return DB::transaction(function () use ($order) {
            $locked = Order::query()
                ->whereKey($order->id)
                ->lockForUpdate()
                ->with(['user', 'items.product'])
                ->first();

            if (! $locked?->user) {
                return null;
            }

            // Idempotent: do not double-credit (duplicate jobs / workers)
            if ((float) $locked->points_earned > 0) {
                return null;
            }
            if (LoyaltyPoint::query()
                ->where('order_id', $locked->id)
                ->where('type', LoyaltyPoint::TYPE_CREDIT)
                ->exists()) {
                return null;
            }

            $amountPaid = max(0, (float) $locked->total_amount - (float) $locked->points_used);
            if ($amountPaid <= 0) {
                return null;
            }

            $rate = $this->resolveRate($locked);
            $points = round($amountPaid * $rate, 2);

            if ($points <= 0) {
                return null;
            }

            $expiresAt = now()->addDays((int) Setting::get('loyalty.validity_days', config('loyalty.validity_days', 30)));

            $lp = LoyaltyPoint::create([
                'user_id' => $locked->user->id,
                'order_id' => $locked->id,
                'type' => LoyaltyPoint::TYPE_CREDIT,
                'points' => $points,
                'description' => 'Earned on order PF-'.str_pad((string) $locked->id, 5, '0', STR_PAD_LEFT),
                'expires_at' => $expiresAt,
            ]);

            $locked->update(['points_earned' => $points]);

            Log::info('Loyalty: credited points', [
                'user_id' => $locked->user->id,
                'order_id' => $locked->id,
                'points' => $points,
                'expires' => $expiresAt->toDateString(),
            ]);

            return $lp;
        });
    }

    /**
     * Debit (redeem) points for a user when they apply points at checkout.
     * Must be called inside a DB transaction, after validating available balance.
     */
    public function debitForOrder(Order $order, float $pointsToUse): ?LoyaltyPoint
    {
        $user = $order->user;
        if (! $user || $pointsToUse <= 0) {
            return null;
        }

        $balance = $this->balance($user);
        if ($pointsToUse > $balance) {
            throw new \RuntimeException("Insufficient loyalty points. Available: {$balance}");
        }

        $lp = LoyaltyPoint::create([
            'user_id' => $user->id,
            'order_id' => $order->id,
            'type' => LoyaltyPoint::TYPE_DEBIT,
            'points' => $pointsToUse,
            'description' => 'Redeemed on order PF-'.str_pad($order->id, 5, '0', STR_PAD_LEFT),
            'expires_at' => null,
        ]);

        Log::info('Loyalty: debited points', [
            'user_id' => $user->id,
            'order_id' => $order->id,
            'points' => $pointsToUse,
        ]);

        return $lp;
    }

    /**
     * Available (non-expired) points balance for a user.
     */
    public function balance(User $user): float
    {
        return $user->loyaltyBalance();
    }

    /**
     * Paginated transaction history for a user.
     */
    public function history(User $user, int $perPage = 15)
    {
        return $user->loyaltyPoints()
            ->with('order')
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Estimate points for a given spend amount using a product's rate (or global default).
     */
    public function estimatePoints(float $amount, ?float $rate = null): float
    {
        $r = $rate ?? (float) Setting::get('loyalty.default_rate', config('loyalty.default_rate', 0.01));

        return round($amount * $r, 2);
    }

    /**
     * Resolve the effective loyalty_rate for an order (from first product, else DB setting, else config).
     */
    protected function resolveRate(Order $order): float
    {
        $product = $order->items->first()?->product;
        $rate = $product?->loyalty_rate ?? Setting::get('loyalty.default_rate', config('loyalty.default_rate', 0.01));

        return (float) $rate;
    }
}
