<?php

namespace App\Jobs;

use App\Services\WoohooProductSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncWoohooProductsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public int $timeout = 600;

    public function __construct(
        public bool $clearToken = false
    ) {
        $this->onQueue('woohoo-products');
    }

    public function handle(WoohooProductSyncService $sync): void
    {
        $sync->sync($this->clearToken);
    }
}
