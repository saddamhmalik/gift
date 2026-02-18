<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\Controller;
use App\Http\Resources\V1\ProductDetailResource;
use App\Http\Resources\V1\ProductResource;
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
        $products = $this->productService->getHotDeals($limit);
        return $this->success(ProductResource::collection($products));
    }

    public function trending(Request $request): JsonResponse
    {
        $limit = min((int) $request->get('limit', 20), 50);
        $products = $this->productService->getTrending($limit);
        return $this->success(ProductResource::collection($products));
    }

    public function bestSellers(Request $request): JsonResponse
    {
        $limit = min((int) $request->get('limit', 20), 50);
        $products = $this->productService->getBestSellers($limit);
        return $this->success(ProductResource::collection($products));
    }

    public function featured(Request $request): JsonResponse
    {
        $limit = min((int) $request->get('limit', 20), 50);
        $products = $this->productService->getFeatured($limit);
        return $this->success(ProductResource::collection($products));
    }

    public function newArrivals(Request $request): JsonResponse
    {
        $limit = min((int) $request->get('limit', 20), 50);
        $days = min((int) $request->get('days', 30), 90);
        $products = $this->productService->getNewArrivals($limit, $days);
        return $this->success(ProductResource::collection($products));
    }

    public function show(Product $product): JsonResponse
    {
        $product = $this->productService->getById($product);
        return $this->success(new ProductDetailResource($product));
    }
}
