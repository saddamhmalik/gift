<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * After a Woohoo Order POST client timeout, wait then poll Order Status API using the refno
 * (saved before throwing) to check whether Woohoo received the order.
 */
class WoohooOrderPostTimeoutJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 60;

    public function __construct(public Order $order)
    {
        $this->onQueue('woohoo-order-poll');
    }

    public function handle(): void
    {
        $order = Order::find($this->order->id);
        if (! $order) {
            return;
        }

        // woohoo_refno is saved before the exception is thrown (outside any transaction),
        // so it must be present for recovery to proceed.
        if (empty($order->woohoo_refno) && empty($order->woohoo_order_id)) {
            Log::warning('Woohoo Order POST timeout: no refno or order_id — cannot poll Status API', [
                'order_id' => $order->id,
            ]);
            return;
        }

        Log::info('Woohoo Order POST timeout recovery: starting Status poll', [
            'order_id'    => $order->id,
            'refno'       => $order->woohoo_refno,
            'woohoo_order_id' => $order->woohoo_order_id,
        ]);
        PollWoohooOrderStatusJob::dispatchForCardSyncDelay($order);
    }
}
