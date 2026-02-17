<?php

namespace App\Console\Commands;

use App\Services\Catalog\HotDealService;
use Illuminate\Console\Command;

class ExpireHotDeals extends Command
{
    protected $signature = 'catalog:expire-hot-deals';

    protected $description = 'Clear deal pricing for expired hot deals';

    public function handle(HotDealService $service): int
    {
        $this->info('Expiring hot deals...');
        $service->expireDeals();
        $this->info('Done.');

        return self::SUCCESS;
    }
}
