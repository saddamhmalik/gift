<?php

namespace App\Repositories;

use App\Models\Product;
use App\Repositories\Contracts\RepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ProductRepository implements RepositoryInterface
{
    public function __construct(
        protected Product $model
    ) {}

    public function find(int $id): ?Product
    {
        return $this->model->find($id);
    }

    public function all(): Collection
    {
        return $this->model->all();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->paginate($perPage);
    }

    public function findBySlug(string $slug): ?Product
    {
        return $this->model
            ->active()
            ->where('slug', $slug)
            ->with(['category', 'tags'])
            ->first();
    }

    public function incrementViews(Product $product): void
    {
        $product->increment('views');
    }

    public function getHotDeals(int $limit = 20): Collection
    {
        return $this->model
            ->active()
            ->hotDeals()
            ->with(['category', 'tags'])
            ->orderByDesc('deal_end')
            ->limit($limit)
            ->get();
    }

    public function getTrending(int $limit = 20): Collection
    {
        return $this->model
            ->active()
            ->trending()
            ->with(['category', 'tags'])
            ->orderByDesc('popularity_score')
            ->limit($limit)
            ->get();
    }

    public function getBestSellers(int $limit = 20): Collection
    {
        return $this->model
            ->active()
            ->bestSellers()
            ->with(['category', 'tags'])
            ->limit($limit)
            ->get();
    }

    public function getFeatured(int $limit = 20): Collection
    {
        return $this->model
            ->active()
            ->featured()
            ->with(['category', 'tags'])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public function getNewArrivals(int $limit = 20, int $days = 30): Collection
    {
        return $this->model
            ->active()
            ->newArrivals($days)
            ->with(['category', 'tags'])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }
}
