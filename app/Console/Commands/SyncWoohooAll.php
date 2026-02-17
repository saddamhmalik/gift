<?php

namespace App\Console\Commands;

use App\Jobs\SyncWoohooAllJob;
use Illuminate\Console\Command;

class SyncWoohooAll extends Command
{
    protected $signature = 'giftbox:sync-all
                            {--clear-token : Clear cached Bearer token before starting}
                            {--skip-details : Skip fetching full product details (categories + product list only)}';

    protected $description = 'Queue full Woohoo sync (categories → products → product details) on Redis via Horizon';

    public function handle(): int
    {
        $clearToken = $this->option('clear-token');
        $skipDetails = $this->option('skip-details');

        SyncWoohooAllJob::dispatch($clearToken, $skipDetails)->onConnection('redis');

        $this->info('Full sync has been queued on Redis. Run Horizon (php artisan horizon) and monitor at /horizon.');
        return self::SUCCESS;
    }
}
