<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\Woohoo\WoohooActivatedCardsService;
use App\Services\Woohoo\WoohooOrderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Fetches activated card details from the Woohoo Activated Cards API and stores
 * them encrypted on the order. Dispatched automatically after order fulfilment
 * (both synchronous 201 and asynchronous COMPLETE polling paths).
 *
 * Retry strategy:
 *  - Woohoo 409 (PROCESSING) → re-queue with exponential back-off; up to $tries times.
 *  - Woohoo 401/400/500      → log and do not re-queue (permanent failure).
 *  - Success                 → store encrypted card details, done.
 */
class FetchActivatedCardsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Max attempts before giving up */
    public int $tries = 10;

    /** Max execution time per attempt (seconds) */
    public int $timeout = 30;

    public function __construct(
        public Order $order,
        public int   $attempt = 0,
    ) {
        $this->onQueue('woohoo-order-poll');
    }

    public function handle(
        WoohooActivatedCardsService $activatedCards,
        WoohooOrderService          $orderService,
    ): void {
        $orderId        = $this->order->id;
        $woohooOrderId  = $this->order->woohoo_order_id;

        if (empty($woohooOrderId)) {
            Log::warning('FetchActivatedCardsJob: no woohoo_order_id', ['order_id' => $orderId]);
            return;
        }

        // Reload fresh from DB to avoid stale state
        $order = Order::find($orderId);
        if (! $order) {
            return;
        }

        Log::info('FetchActivatedCardsJob: fetching cards', [
            'order_id'       => $orderId,
            'woohoo_order_id'=> $woohooOrderId,
            'attempt'        => $this->attempt + 1,
        ]);

        $result = $activatedCards->fetchAndNormalize($woohooOrderId);

        if ($result['success'] && ! empty($result['cards'])) {
            $orderService->storeCardDetailsEncrypted($order, $result);
            Log::info('FetchActivatedCardsJob: cards stored', [
                'order_id'    => $orderId,
                'total_cards' => count($result['cards']),
            ]);
            return;
        }

        $httpStatus = $result['http_status'];

        // 409 = cards still activating → retry with exponential back-off
        if ($httpStatus === WoohooActivatedCardsService::STATUS_PROCESSING) {
            $nextAttempt = $this->attempt + 1;
            if ($nextAttempt >= $this->tries) {
                Log::error('FetchActivatedCardsJob: max retries reached (still PROCESSING)', [
                    'order_id' => $orderId,
                ]);
                return;
            }

            // Back-off: 5s, 10s, 20s, 40s, … up to 5 min
            $delaySec = min(300, 5 * (2 ** $this->attempt));
            Log::info('FetchActivatedCardsJob: cards still processing, retrying', [
                'order_id'     => $orderId,
                'delay_sec'    => $delaySec,
                'next_attempt' => $nextAttempt,
            ]);

            static::dispatch($order, $nextAttempt)->delay(now()->addSeconds($delaySec));
            return;
        }

        // 410 = order cancelled; 401/400/500 = permanent failure
        Log::error('FetchActivatedCardsJob: permanent failure, giving up', [
            'order_id'    => $orderId,
            'http_status' => $httpStatus,
            'state'       => $result['state']   ?? null,
            'message'     => $result['message'] ?? null,
        ]);
    }
}
