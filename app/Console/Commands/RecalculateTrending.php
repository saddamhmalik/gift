<?php

namespace App\Console\Commands;

use App\Services\Catalog\TrendingService;
use Illuminate\Console\Command;

class RecalculateTrending extends Command
{
    protected $signature = 'catalog:recalculate-trending';

    protected $description = 'Recalculate popularity_score and is_trending for products';

    public function handle(TrendingService $service): int
    {
        $this->info('Recalculating trending products...');
        $service->recalculate();
        $this->info('Done.');

        return self::SUCCESS;
    }
}
