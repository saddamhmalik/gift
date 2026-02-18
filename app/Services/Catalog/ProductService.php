<?php

namespace App\Services\Catalog;

use App\Http\Resources\V1\ProductDetailResource;
use App\Http\Resources\V1\ProductResource;
use App\Models\Product;
use App\Repositories\ProductRepository;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
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

    public function getHotDeals(int $limit = self::DEFAULT_LIMIT): AnonymousResourceCollection
    {
        $key = "api:products:hot_deals:{$limit}";
        $products = Cache::remember($key, self::CACHE_TTL_HOT_DEALS, fn () => $this->repository->getHotDeals($limit));
        return ProductResource::collection($products);
    }

    public function getTrending(int $limit = self::DEFAULT_LIMIT): AnonymousResourceCollection
    {
        $key = "api:products:trending:{$limit}";
        $products = Cache::remember($key, self::CACHE_TTL_TRENDING, fn () => $this->repository->getTrending($limit));
        return ProductResource::collection($products);
    }

    public function getBestSellers(int $limit = self::DEFAULT_LIMIT): AnonymousResourceCollection
    {
        $key = "api:products:best_sellers:{$limit}";
        $products = Cache::remember($key, self::CACHE_TTL_BEST_SELLERS, fn () => $this->repository->getBestSellers($limit));
        return ProductResource::collection($products);
    }

    public function getFeatured(int $limit = self::DEFAULT_LIMIT): AnonymousResourceCollection
    {
        $key = "api:products:featured:{$limit}";
        $products = Cache::remember($key, self::CACHE_TTL_FEATURED, fn () => $this->repository->getFeatured($limit));
        return ProductResource::collection($products);
    }

    public function getNewArrivals(int $limit = self::DEFAULT_LIMIT, int $days = 30): AnonymousResourceCollection
    {
        $key = "api:products:new_arrivals:{$limit}:{$days}";
        $products = Cache::remember($key, self::CACHE_TTL_NEW_ARRIVALS, fn () => $this->repository->getNewArrivals($limit, $days));
        return ProductResource::collection($products);
    }

    public function getById(Product $product): ProductDetailResource
    {
        $product->loadMissing(['category', 'tags']);
        if (!$product->is_active) {
            abort(404);
        }
        $this->repository->incrementViews($product);
        $product->refresh();
        return new ProductDetailResource($product);
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
