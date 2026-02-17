<?php

namespace App\Console\Commands;

use App\Services\WoohooProductSyncService;
use Illuminate\Console\Command;

class FetchWoohooProducts extends Command
{
    protected $signature = 'giftbox:fetch-products
                            {--clear-token : Clear cached Bearer token before fetch}
                            {--category= : Sync only this category external_id}';

    protected $description = 'Fetch product list from Woohoo API per category and store in database';

    public function handle(WoohooProductSyncService $sync): int
    {
        if ($this->option('clear-token')) {
            app(\App\Services\WoohooClient::class)->clearCachedToken();
        }

        $categoryId = $this->option('category');
        if ($categoryId !== null) {
            $category = \App\Models\Category::where('external_id', $categoryId)->first();
            if (! $category) {
                $this->error("Category with external_id '{$categoryId}' not found. Sync categories first.");
                return self::FAILURE;
            }
            $result = $sync->syncProductsForCategory($category);
            $this->info("Fetched and stored {$result['synced']} products for category {$category->name}.");
            if (isset($result['error'])) {
                $this->warn($result['error']);
            }
            return self::SUCCESS;
        }

        $result = $sync->sync($this->option('clear-token'));

        if (! $result['success'] && $result['synced'] === 0) {
            $this->error($result['message'] ?? 'Failed to fetch products');
            return self::FAILURE;
        }

        $this->info("Fetched and stored {$result['synced']} products.");
        if (! empty($result['message'])) {
            $this->warn($result['message']);
        }
        return self::SUCCESS;
    }
}
