<?php

namespace App\Console\Commands;

use App\Services\WoohooCategorySyncService;
use App\Services\WoohooProductDetailService;
use App\Services\WoohooProductSyncService;
use Illuminate\Console\Command;

class SyncWoohooAll extends Command
{
    protected $signature = 'giftbox:sync-all
                            {--clear-token : Clear cached Bearer token before starting}
                            {--skip-details : Skip fetching full product details (categories + product list only)}';

    protected $description = 'Fetch categories, then product list, then product details from Woohoo API in sequence';

    public function handle(
        WoohooCategorySyncService $categorySync,
        WoohooProductSyncService $productSync,
        WoohooProductDetailService $detailSync
    ): int {
        $clearToken = $this->option('clear-token');
        $skipDetails = $this->option('skip-details');

        if ($clearToken) {
            app(\App\Services\WoohooClient::class)->clearCachedToken();
        }

        $this->info('Step 1/3: Fetching categories...');
        $catResult = $categorySync->sync($clearToken);
        if (! $catResult['success']) {
            $this->error($catResult['message'] ?? 'Failed to fetch categories');
            return self::FAILURE;
        }
        $this->info("  → Synced {$catResult['synced']} categories.");

        $this->newLine();
        $this->info('Step 2/3: Fetching product list per category...');
        $prodResult = $productSync->sync(false);
        if (! $prodResult['success'] && $prodResult['synced'] === 0) {
            $this->error($prodResult['message'] ?? 'Failed to fetch product list');
            return self::FAILURE;
        }
        $this->info("  → Synced {$prodResult['synced']} products.");
        if (! empty($prodResult['message'])) {
            $this->warn("  → {$prodResult['message']}");
        }

        if ($skipDetails) {
            $this->newLine();
            $this->info('Skipping product details (--skip-details).');
            return self::SUCCESS;
        }

        $this->newLine();
        $this->info('Step 3/3: Fetching product details by SKU...');
        $detailResult = $detailSync->syncAll(false);
        $this->info("  → Synced details for {$detailResult['synced']} product(s).");
        if ($detailResult['failed'] > 0) {
            $this->warn("  → {$detailResult['failed']} product(s) failed.");
        }

        $this->newLine();
        $this->info('Sync complete.');
        return self::SUCCESS;
    }
}
