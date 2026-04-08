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
 * QC: after Order POST client timeout, wait 40s then run Status polling if we have woohoo_order_id.
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

        if (empty($order->woohoo_order_id)) {
            Log::warning('Woohoo Order POST timeout: no woohoo_order_id for Status API', [
                'order_id' => $order->id,
                'refno'    => $order->woohoo_refno,
            ]);
            return;
        }

        Log::info('Woohoo Order POST timeout recovery: starting Status poll', ['order_id' => $order->id]);
        PollWoohooOrderStatusJob::dispatchForCardSyncDelay($order);
    }
}
