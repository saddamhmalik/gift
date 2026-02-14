<?php

namespace App\Console\Commands;

use App\Services\WoohooCategorySyncService;
use Illuminate\Console\Command;

class FetchWoohooCategories extends Command
{
    protected $signature = 'giftbox:fetch-categories
                            {--clear-token : Clear cached Bearer token before fetch}';

    protected $description = 'Fetch categories from Woohoo API and store in database';

    public function handle(WoohooCategorySyncService $sync): int
    {
        $result = $sync->sync($this->option('clear-token'));

        if (! $result['success']) {
            $this->error($result['message'] ?? 'Failed to fetch categories');
            return self::FAILURE;
        }

        $this->info("Fetched and stored {$result['synced']} categories.");
        return self::SUCCESS;
    }
}
