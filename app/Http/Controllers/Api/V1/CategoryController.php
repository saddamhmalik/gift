<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\Controller;
use App\Http\Resources\V1\CategoryResource;
use App\Services\Catalog\CategoryService;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function __construct(
        protected CategoryService $categoryService
    ) {}

    public function index(): JsonResponse
    {
        $categories = $this->categoryService->getParentCategoriesWithSubcategories();
        return $this->success(CategoryResource::collection($categories));
    }

    public function show(string $slug): JsonResponse
    {
        $category = $this->categoryService->getBySlug($slug);
        if (!$category) {
            return $this->error('Category not found', 404);
        }
        $category->load('children');
        return $this->success(new CategoryResource($category));
    }
}
