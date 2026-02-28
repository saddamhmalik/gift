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
     * Credit points to a user after a successful order.
     * Points = amountPaid * product's loyalty_rate (fallback to global default).
     * Only the real-money portion of the order earns points (not the points-paid portion).
     */
    public function creditForOrder(Order $order): LoyaltyPoint|null
    {
        $user = $order->user;
        if (! $user) {
            return null;
        }

        // Amount that earned points = total_amount - points_used
        $amountPaid = max(0, (float) $order->total_amount - (float) $order->points_used);
        if ($amountPaid <= 0) {
            return null;
        }

        // Determine rate from first product, fallback to global config
        $rate = $this->resolveRate($order);
        $points = round($amountPaid * $rate, 2);

        if ($points <= 0) {
            return null;
        }

        $expiresAt = now()->addDays((int) Setting::get('loyalty.validity_days', config('loyalty.validity_days', 30)));

        $lp = LoyaltyPoint::create([
            'user_id'     => $user->id,
            'order_id'    => $order->id,
            'type'        => LoyaltyPoint::TYPE_CREDIT,
            'points'      => $points,
            'description' => 'Earned on order #' . $order->id,
            'expires_at'  => $expiresAt,
        ]);

        $order->update(['points_earned' => $points]);

        Log::info('Loyalty: credited points', [
            'user_id'  => $user->id,
            'order_id' => $order->id,
            'points'   => $points,
            'expires'  => $expiresAt->toDateString(),
        ]);

        return $lp;
    }

    /**
     * Debit (redeem) points for a user when they apply points at checkout.
     * Must be called inside a DB transaction, after validating available balance.
     */
    public function debitForOrder(Order $order, float $pointsToUse): LoyaltyPoint|null
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
            'user_id'     => $user->id,
            'order_id'    => $order->id,
            'type'        => LoyaltyPoint::TYPE_DEBIT,
            'points'      => $pointsToUse,
            'description' => 'Redeemed on order #' . $order->id,
            'expires_at'  => null,
        ]);

        Log::info('Loyalty: debited points', [
            'user_id'  => $user->id,
            'order_id' => $order->id,
            'points'   => $pointsToUse,
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
        $rate    = $product?->loyalty_rate ?? Setting::get('loyalty.default_rate', config('loyalty.default_rate', 0.01));
        return (float) $rate;
    }
}
