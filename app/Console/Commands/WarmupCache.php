<?php

namespace App\Console\Commands;

use App\Services\Catalog\CacheWarmupService;
use Illuminate\Console\Command;

class WarmupCache extends Command
{
    protected $signature = 'catalog:warmup-cache';

    protected $description = 'Warm up API response cache';

    public function handle(CacheWarmupService $service): int
    {
        $this->info('Warming up cache...');
        $service->warmUp();
        $this->info('Done.');

        return self::SUCCESS;
    }
}
