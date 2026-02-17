<?php

namespace App\Jobs;

use App\Services\WoohooCategorySyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncWoohooCategoriesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public int $timeout = 300;

    public function __construct(
        public bool $clearToken = false
    ) {
        $this->onQueue('woohoo-categories');
    }

    public function handle(WoohooCategorySyncService $sync): void
    {
        $sync->sync($this->clearToken);
    }
}
