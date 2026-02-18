<?php

namespace App\Services\Catalog;

use App\Models\Product;
use App\Repositories\ProductRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class ProductService
{
    private const CACHE_TTL_HOT_DEALS = 300;      // 5 min
    private const CACHE_TTL_TRENDING = 600;       // 10 min
    private const CACHE_TTL_BEST_SELLERS = 600;   // 10 min
    private const CACHE_TTL_FEATURED = 600;       // 10 min
    private const CACHE_TTL_NEW_ARRIVALS = 900;   // 15 min
    private const DEFAULT_LIMIT = 20;

    public function __construct(
        protected ProductRepository $repository
    ) {}

    public function getHotDeals(int $limit = self::DEFAULT_LIMIT): Collection
    {
        $key = "api:products:hot_deals:{$limit}";
        return Cache::remember($key, self::CACHE_TTL_HOT_DEALS, fn () => $this->repository->getHotDeals($limit));
    }

    public function getTrending(int $limit = self::DEFAULT_LIMIT): Collection
    {
        $key = "api:products:trending:{$limit}";
        return Cache::remember($key, self::CACHE_TTL_TRENDING, fn () => $this->repository->getTrending($limit));
    }

    public function getBestSellers(int $limit = self::DEFAULT_LIMIT): Collection
    {
        $key = "api:products:best_sellers:{$limit}";
        return Cache::remember($key, self::CACHE_TTL_BEST_SELLERS, fn () => $this->repository->getBestSellers($limit));
    }

    public function getFeatured(int $limit = self::DEFAULT_LIMIT): Collection
    {
        $key = "api:products:featured:{$limit}";
        return Cache::remember($key, self::CACHE_TTL_FEATURED, fn () => $this->repository->getFeatured($limit));
    }

    public function getNewArrivals(int $limit = self::DEFAULT_LIMIT, int $days = 30): Collection
    {
        $key = "api:products:new_arrivals:{$limit}:{$days}";
        return Cache::remember($key, self::CACHE_TTL_NEW_ARRIVALS, fn () => $this->repository->getNewArrivals($limit, $days));
    }

    public function getById(Product $product): Product
    {
        $product->loadMissing(['category', 'tags']);
        if (! $product->is_active) {
            abort(404);
        }
        $this->repository->incrementViews($product);
        $product->refresh();

        return $product;
    }

    public static function clearProductListCache(): void
    {
        $limits = [10, 20, 50];
        $prefixes = ['hot_deals', 'trending', 'best_sellers', 'featured'];
        foreach ($prefixes as $prefix) {
            foreach ($limits as $limit) {
                Cache::forget("api:products:{$prefix}:{$limit}");
            }
        }
        foreach ($limits as $limit) {
            foreach ([30, 60, 90] as $days) {
                Cache::forget("api:products:new_arrivals:{$limit}:{$days}");
            }
        }
    }
}
