<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\Loyalty\LoyaltyService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Credits loyalty points for the cash portion of a paid order after a delay.
 * Redeemed points are debited immediately in PayUController; earned points follow this job.
 */
class CreditLoyaltyForOrderJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Keep unique lock long enough to cover delayed dispatch + retries. */
    public int $uniqueFor = 86400;

    public int $tries = 3;

    public int $timeout = 60;

    public function __construct(public Order $order)
    {
        $this->onQueue('default');
    }

    public function uniqueId(): string
    {
        return 'loyalty-credit-order-'.$this->order->id;
    }

    public function handle(LoyaltyService $loyaltyService): void
    {
        $order = Order::query()
            ->with(['user', 'items.product'])
            ->find($this->order->id);

        if (! $order || ! $order->user) {
            return;
        }

        if ($order->status !== Order::STATUS_COMPLETED) {
            Log::info('CreditLoyaltyForOrderJob: skip — order not completed', [
                'order_id' => $order->id,
                'status' => $order->status,
            ]);

            return;
        }

        if (in_array($order->refund_status, [
            Order::REFUND_STATUS_PENDING,
            Order::REFUND_STATUS_REFUNDED,
        ], true)) {
            Log::info('CreditLoyaltyForOrderJob: skip — refund pending or completed', [
                'order_id' => $order->id,
                'refund_status' => $order->refund_status,
            ]);

            return;
        }

        $loyaltyService->creditForOrder($order);
    }
}
