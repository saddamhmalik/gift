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

    public function search(string $query, array $filters = [], int $perPage = 18): LengthAwarePaginator
    {
        $q = trim($query);

        return $this->model
            ->active()
            ->when($q, function ($builder) use ($q) {
                $builder->where(function ($inner) use ($q) {
                    $inner->where('name', 'like', "%{$q}%")
                          ->orWhere('offer_short_desc', 'like', "%{$q}%")
                          ->orWhere('description', 'like', "%{$q}%")
                          ->orWhereHas('category', fn ($c) => $c->where('name', 'like', "%{$q}%"))
                          ->orWhereHas('tags', fn ($t) => $t->where('name', 'like', "%{$q}%"));
                });
            })
            ->when(!empty($filters['category']), fn ($b) =>
                $b->whereHas('category', fn ($c) => $c->where('slug', $filters['category']))
            )
            ->when(!empty($filters['min_price']), fn ($b) =>
                $b->where('min_price', '>=', (float) $filters['min_price'])
            )
            ->when(!empty($filters['max_price']), fn ($b) =>
                $b->where('min_price', '<=', (float) $filters['max_price'])
            )
            ->when(!empty($filters['sort']), function ($b) use ($filters) {
                match ($filters['sort']) {
                    'price_asc'  => $b->orderBy('min_price', 'asc'),
                    'price_desc' => $b->orderBy('min_price', 'desc'),
                    'newest'     => $b->orderByDesc('created_at'),
                    'popular'    => $b->orderByDesc('views'),
                    default      => $b->orderByDesc('created_at'),
                };
            }, fn ($b) => $b->orderByDesc('views'))
            ->with(['category', 'tags'])
            ->paginate($perPage);
    }

    public function getByTag(string $tagSlug, int $perPage = 12): LengthAwarePaginator
    {
        return $this->model
            ->active()
            ->whereHas('tags', fn ($q) => $q->where('slug', $tagSlug)->where('is_active', true))
            ->with(['category', 'tags'])
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }
}
