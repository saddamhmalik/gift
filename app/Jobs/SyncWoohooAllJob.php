<?php

namespace App\Jobs;

use App\Services\WoohooClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;

class SyncWoohooAllJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public int $timeout = 60;

    public function __construct(
        public bool $clearToken = false,
        public bool $skipDetails = false
    ) {
        $this->onQueue('woohoo-categories');
    }

    public function handle(): void
    {
        if ($this->clearToken) {
            app(WoohooClient::class)->clearCachedToken();
        }

        $chain = [
            new SyncWoohooCategoriesJob(false),
            new SyncWoohooProductsJob(false),
            ...$this->skipDetails ? [] : [new SyncWoohooProductDetailsJob(false)],
        ];

        Bus::connection('redis')->chain($chain)->dispatch();
    }
}
