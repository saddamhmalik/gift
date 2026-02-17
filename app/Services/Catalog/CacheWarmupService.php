<?php

namespace App\Services\Catalog;

use Illuminate\Support\Facades\Cache;

class CacheWarmupService
{
    public function warmUp(): void
    {
        $lock = Cache::lock('cache_warmup', 600);
        if (!$lock->get()) {
            return;
        }

        try {
            $categoryService = app(CategoryService::class);
            $productService = app(ProductService::class);

            $categoryService->getParentCategoriesWithSubcategories();
            $productService->getHotDeals(20);
            $productService->getTrending(20);
            $productService->getBestSellers(20);
            $productService->getFeatured(20);
            $productService->getNewArrivals(20, 30);
        } finally {
            $lock->release();
        }
    }
}
