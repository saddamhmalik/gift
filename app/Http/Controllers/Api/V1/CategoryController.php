<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\Controller;
use App\Services\Catalog\CategoryService;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function __construct(
        protected CategoryService $categoryService
    ) {}

    public function index(): JsonResponse
    {
        $data = $this->categoryService->getParentCategoriesWithSubcategories();
        return $this->success($data);
    }

    public function show(string $slug): JsonResponse
    {
        $category = $this->categoryService->getBySlug($slug);
        if (!$category) {
            return $this->error('Category not found', 404);
        }
        $category->load('children');
        return $this->success(new \App\Http\Resources\V1\CategoryResource($category));
    }
}
