<?php

namespace App\Services\Catalog;

use App\Models\Product;
use Illuminate\Support\Facades\Cache;

class TrendingService
{
    /**
     * Calculate popularity_score from views + total_sales and set is_trending for top N.
     */
    public function recalculate(): void
    {
        $lock = Cache::lock('trending_calculation', 300);
        if (!$lock->get()) {
            return;
        }

        try {
            $topCount = (int) config('catalog.trending_top_count', 50);

            Product::query()
                ->where('is_active', true)
                ->chunkById(200, function ($products) {
                    foreach ($products as $product) {
                        $score = ($product->views ?? 0) * 2 + ($product->total_sales ?? 0) * 10;
                        $product->update(['popularity_score' => $score]);
                    }
                });

            $ids = Product::query()
                ->where('is_active', true)
                ->orderByDesc('popularity_score')
                ->limit($topCount)
                ->pluck('id');

            Product::query()->update(['is_trending' => false]);
            Product::query()->whereIn('id', $ids)->update(['is_trending' => true]);
        } finally {
            $lock->release();
        }
    }
}
