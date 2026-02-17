<?php

namespace App\Repositories;

use App\Models\Category;
use App\Repositories\Contracts\RepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class CategoryRepository implements RepositoryInterface
{
    public function __construct(
        protected Category $model
    ) {}

    public function find(int $id): ?Category
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

    public function getParentCategoriesWithSubcategories(): Collection
    {
        return $this->model
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->with(['children' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')->orderBy('name')])
            ->get();
    }

    public function findBySlug(string $slug): ?Category
    {
        return $this->model->where('slug', $slug)->where('is_active', true)->first();
    }
}
