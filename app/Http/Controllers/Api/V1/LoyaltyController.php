<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\Controller;
use App\Services\Loyalty\LoyaltyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoyaltyController extends Controller
{
    public function __construct(protected LoyaltyService $loyaltyService) {}

    /**
     * GET /api/v1/loyalty/balance
     * Returns the authenticated user's points balance and program config.
     */
    public function balance(Request $request): JsonResponse
    {
        $user    = $request->user();
        $balance = $this->loyaltyService->balance($user);

        // Upcoming expirations (next 7 days)
        $expiringSoon = $user->loyaltyPoints()
            ->where('type', 'credit')
            ->where('expires_at', '>', now())
            ->where('expires_at', '<=', now()->addDays(7))
            ->sum('points');

        // Lifetime earned
        $lifetimeEarned = (float) $user->loyaltyPoints()
            ->where('type', 'credit')
            ->sum('points');

        // Lifetime redeemed
        $lifetimeRedeemed = (float) $user->loyaltyPoints()
            ->where('type', 'debit')
            ->sum('points');

        return $this->success([
            'balance'           => (float) $balance,
            'expiring_soon'     => (float) $expiringSoon,
            'lifetime_earned'   => $lifetimeEarned,
            'lifetime_redeemed' => $lifetimeRedeemed,
            'default_rate'      => (float) config('loyalty.default_rate', 0.01),
            'validity_days'     => (int) config('loyalty.validity_days', 30),
            'value_per_point'   => 1.0, // 1 point = ₹1
        ]);
    }

    /**
     * GET /api/v1/loyalty/history
     * Returns paginated loyalty transaction history for the authenticated user.
     */
    public function history(Request $request): JsonResponse
    {
        $user    = $request->user();
        $perPage = min((int) $request->get('per_page', 15), 50);
        $history = $this->loyaltyService->history($user, $perPage);

        $items = $history->getCollection()->map(fn ($lp) => [
            'id'          => $lp->id,
            'type'        => $lp->type,
            'points'      => (float) $lp->points,
            'description' => $lp->description,
            'expires_at'  => $lp->expires_at?->toDateString(),
            'is_expired'  => $lp->is_expired,
            'order_id'    => $lp->order_id,
            'created_at'  => $lp->created_at->toDateTimeString(),
        ]);

        return $this->success([
            'data' => $items,
            'meta' => [
                'current_page' => $history->currentPage(),
                'last_page'    => $history->lastPage(),
                'per_page'     => $history->perPage(),
                'total'        => $history->total(),
            ],
        ]);
    }

    /**
     * GET /api/v1/loyalty/estimate
     * Estimate points for a given amount (and optional product).
     */
    public function estimate(Request $request): JsonResponse
    {
        $request->validate(['amount' => 'required|numeric|min:1']);

        $amount   = (float) $request->amount;
        $rate     = (float) config('loyalty.default_rate', 0.01);
        $points   = $this->loyaltyService->estimatePoints($amount, $rate);

        return $this->success([
            'amount'         => $amount,
            'rate'           => $rate,
            'points_to_earn' => $points,
        ]);
    }
}
