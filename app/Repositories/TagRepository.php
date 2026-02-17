<?php

namespace App\Repositories;

use App\Models\Product;
use App\Models\Tag;
use App\Repositories\Contracts\RepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class TagRepository implements RepositoryInterface
{
    public function __construct(
        protected Tag $model
    ) {}

    public function find(int $id): ?Tag
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

    public function getActive(): Collection
    {
        return $this->model
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function findBySlug(string $slug): ?Tag
    {
        return $this->model->where('slug', $slug)->first();
    }

    public function syncProductTags(Product $product, array $tagIds): void
    {
        $product->tags()->sync($tagIds);
    }
}
