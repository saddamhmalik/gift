<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Services\WoohooProductDetailService;
use Illuminate\Console\Command;

class FetchWoohooProductDetails extends Command
{
    protected $signature = 'giftbox:fetch-product-details
                            {--clear-token : Clear cached Bearer token before fetch}
                            {--sku= : Sync only this product SKU}';

    protected $description = 'Fetch full product attributes from Woohoo Product API by SKU and update database';

    public function handle(WoohooProductDetailService $sync): int
    {
        if ($this->option('clear-token')) {
            app(\App\Services\WoohooClient::class)->clearCachedToken();
        }

        $sku = $this->option('sku');
        if ($sku !== null) {
            $product = Product::where('external_id', $sku)->first();
            if (! $product) {
                $this->error("Product with SKU '{$sku}' not found. Sync product list first.");
                return self::FAILURE;
            }
            $ok = $sync->syncProductDetails($product);
            if ($ok) {
                $this->info("Fetched and updated product details for SKU {$sku}.");
            } else {
                $this->error("Failed to fetch product details for SKU {$sku}.");
                return self::FAILURE;
            }
            return self::SUCCESS;
        }

        $result = $sync->syncAll($this->option('clear-token'));

        $this->info("Fetched and updated {$result['synced']} product details.");
        if ($result['failed'] > 0) {
            $this->warn("{$result['failed']} product(s) failed.");
        }
        return $result['failed'] > 0 && $result['synced'] === 0 ? self::FAILURE : self::SUCCESS;
    }
}
