<?php

namespace App\Jobs;

use App\Services\WoohooProductDetailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncWoohooProductDetailsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public int $timeout = 3600;

    public function __construct(
        public bool $clearToken = false
    ) {
        $this->onQueue('woohoo-product-details');
    }

    public function handle(WoohooProductDetailService $sync): void
    {
        $sync->syncAll($this->clearToken);
    }
}
