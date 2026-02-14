<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Category::query()->where('is_active', true);

        if (! $request->boolean('all')) {
            $query->whereNull('parent_id');
        }
        if ($request->filled('parent_id')) {
            $query->where('parent_id', $request->parent_id);
        }

        $query->orderBy('sort_order')->orderBy('name');
        $query->when($request->boolean('with_children'), fn ($q) => $q->with(['children' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')->orderBy('name')]));
        $query->when($request->boolean('with_brands'), fn ($q) => $q->with(['brands' => fn ($q) => $q->where('is_active', true)]));

        $categories = $query->get();

        return $this->success($categories);
    }

    public function show(Category $category): JsonResponse
    {
        if (! $category->is_active) {
            return $this->error('Category not found', 404);
        }

        $category->load(['brands' => fn ($q) => $q->where('is_active', true)]);
        if (request()->boolean('with_children')) {
            $category->load(['children' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')->orderBy('name')]);
        }
        if (request()->boolean('with_parent')) {
            $category->load('parent');
        }

        return $this->success($category);
    }
}
