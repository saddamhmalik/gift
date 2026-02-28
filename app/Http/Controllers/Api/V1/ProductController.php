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

    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'q'         => 'nullable|string|max:100',
            'category'  => 'nullable|string|max:100',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'sort'      => 'nullable|in:price_asc,price_desc,newest,popular',
            'per_page'  => 'nullable|integer|min:1|max:48',
        ]);

        $q       = trim($request->get('q', ''));
        $perPage = min((int) $request->get('per_page', 18), 48);
        $filters = $request->only(['category', 'min_price', 'max_price', 'sort']);

        $results = $this->productService->search($q, $filters, $perPage);

        return $this->success([
            'data' => ProductResource::collection($results),
            'meta' => [
                'current_page' => $results->currentPage(),
                'last_page'    => $results->lastPage(),
                'per_page'     => $results->perPage(),
                'total'        => $results->total(),
                'query'        => $q,
            ],
        ]);
    }

    public function show(Product $product): JsonResponse
    {
        $product = $this->productService->getById($product);
        return $this->success(new ProductDetailResource($product));
    }
}
