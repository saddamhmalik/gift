<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\Controller;
use App\Models\Product;
use App\Services\Catalog\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(
        protected ProductService $productService
    ) {}

    public function hotDeals(Request $request): JsonResponse
    {
        $limit = min((int) $request->get('limit', 20), 50);
        return $this->success($this->productService->getHotDeals($limit));
    }

    public function trending(Request $request): JsonResponse
    {
        $limit = min((int) $request->get('limit', 20), 50);
        return $this->success($this->productService->getTrending($limit));
    }

    public function bestSellers(Request $request): JsonResponse
    {
        $limit = min((int) $request->get('limit', 20), 50);
        return $this->success($this->productService->getBestSellers($limit));
    }

    public function featured(Request $request): JsonResponse
    {
        $limit = min((int) $request->get('limit', 20), 50);
        return $this->success($this->productService->getFeatured($limit));
    }

    public function newArrivals(Request $request): JsonResponse
    {
        $limit = min((int) $request->get('limit', 20), 50);
        $days = min((int) $request->get('days', 30), 90);
        return $this->success($this->productService->getNewArrivals($limit, $days));
    }

    public function show(Product $product): JsonResponse
    {
        $result = $this->productService->getById($product);
        return $this->success($result);
    }
}
