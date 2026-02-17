<?php

namespace App\Services\Catalog;

use App\Models\Product;
use Illuminate\Support\Facades\Cache;

class HotDealService
{
    /**
     * Clear deal_price, deal_start, deal_end for expired deals.
     */
    public function expireDeals(): void
    {
        $lock = Cache::lock('hot_deal_expiry', 60);
        if (!$lock->get()) {
            return;
        }

        try {
            Product::query()
                ->whereNotNull('deal_end')
                ->where('deal_end', '<', now())
                ->update([
                    'deal_price' => null,
                    'deal_start' => null,
                    'deal_end' => null,
                ]);
        } finally {
            $lock->release();
        }
    }
}
