<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\Woohoo\WoohooOrderStatusService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Poll Woohoo Order Status API after 202 async response or client timeout.
 * Uses GET /rest/v3/order/{refno}/status (preferred) or GET /rest/v3/orders/{orderId} (fallback).
 * On COMPLETE → triggers Activated Cards API to retrieve card details.
 * Max checks + delays are configurable via woohoo.status_poll config.
 */
class PollWoohooOrderStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 120;

    public function __construct(
        public Order $order,
        public int $checkIndex = 0,
    ) {
        $this->onQueue('woohoo-order-poll');
    }

    /** After Woohoo returns 202 — first Status check after configurable delay (default 120s). */
    public static function dispatchAfterAsyncOrder(Order $order): void
    {
        $delaySec = (int) config('woohoo.status_poll.first_delay_sec', 120);
        static::dispatch($order, 0)->delay(now()->addSeconds($delaySec));
    }

    /** When 201 but cards not ready — run Status API before more Activated Cards calls. */
    public static function dispatchForCardSyncDelay(Order $order): void
    {
        static::dispatch($order, 0);
    }

    public function handle(WoohooOrderStatusService $statusService): void
    {
        $order = Order::find($this->order->id);
        if (! $order || (empty($order->woohoo_refno) && empty($order->woohoo_order_id))) {
            Log::warning('PollWoohooOrderStatusJob: missing order or both refno and woohoo_order_id', [
                'order_id' => $this->order->id,
            ]);
            return;
        }

        $maxChecks = max(1, (int) config('woohoo.status_poll.max_checks', 3));

        Log::info('PollWoohooOrderStatusJob: calling Order Status API', [
            'order_id'        => $order->id,
            'woohoo_refno'    => $order->woohoo_refno,
            'woohoo_order_id' => $order->woohoo_order_id,
            'check'           => $this->checkIndex + 1,
            'max_checks'      => $maxChecks,
        ]);

        $data    = $statusService->fetchStatusForPolling($order);
        $outcome = $statusService->applyOrderDetailsSnapshot($order, $data);

        if ($outcome !== 'processing') {
            return;
        }

        if ($this->checkIndex >= $maxChecks - 1) {
            Log::warning('PollWoohooOrderStatusJob: max Status API checks reached, still PROCESSING', [
                'order_id' => $order->id,
            ]);
            return;
        }

        $delaySec = match ($this->checkIndex) {
            0 => (int) config('woohoo.status_poll.second_delay_sec', 300),
            default => (int) config('woohoo.status_poll.third_delay_sec', 600),
        };

        static::dispatch($order, $this->checkIndex + 1)->delay(now()->addSeconds($delaySec));
    }
}
