<?php

namespace App\Services\Catalog;

use App\Repositories\CategoryRepository;
use Illuminate\Support\Facades\Cache;

class CategoryService
{
    private const CACHE_KEY = 'api:categories:parent_with_children';
    private const CACHE_TTL = 86400; // 24 hours

    public function __construct(
        protected CategoryRepository $repository
    ) {}

    public function getParentCategoriesWithSubcategories()
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return $this->repository->getParentCategoriesWithSubcategories();
        });
    }

    public function getBySlug(string $slug)
    {
        return $this->repository->findBySlug($slug);
    }

    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
