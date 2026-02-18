<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\Woohoo\WoohooOrderStatusService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PollWoohooOrderStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     * Poll job runs up to ~5 min (30 attempts × 10s interval).
     */
    public int $timeout = 360;

    public function __construct(
        public Order $order,
        public int $intervalSeconds = 10,
        public int $maxAttempts = 30
    ) {
        $this->onQueue('woohoo-order-poll');
    }

    public function handle(WoohooOrderStatusService $statusService): void
    {
        $statusService->pollUntilComplete($this->order, $this->intervalSeconds, $this->maxAttempts);
    }
}
