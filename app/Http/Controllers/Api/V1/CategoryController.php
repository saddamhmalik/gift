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

        $categories = $query->get();

        return $this->success($categories);
    }

    public function show(Request $request, Category $category): JsonResponse
    {
        if (! $category->is_active) {
            return $this->error('Category not found', 404);
        }

        $withChildren = $request->boolean('with_children');
        $withParent = $request->boolean('with_parent');
        if ($withChildren || $withParent) {
            $relations = [];
            if ($withChildren) {
                $relations['children'] = fn ($q) => $q->where('is_active', true)->orderBy('sort_order')->orderBy('name');
            }
            if ($withParent) {
                $relations['parent'] = static function ($q) {
                    // load parent with no extra constraints
                };
            }
            $category->load($relations);
        }

        return $this->success($category);
    }
}
