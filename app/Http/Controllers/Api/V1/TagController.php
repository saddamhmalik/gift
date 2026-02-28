<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\Controller;
use App\Http\Resources\V1\ProductResource;
use App\Http\Resources\V1\TagResource;
use App\Models\Tag;
use App\Services\Catalog\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function __construct(
        protected ProductService $productService
    ) {}

    /**
     * List all active tags.
     */
    public function index(): JsonResponse
    {
        $tags = Tag::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return $this->success(TagResource::collection($tags));
    }

    /**
     * Show a single tag with its paginated products.
     */
    public function show(string $slug, Request $request): JsonResponse
    {
        $tag = Tag::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $perPage  = min((int) $request->get('per_page', 12), 50);
        $products = $this->productService->getByTag($slug, $perPage);

        return $this->success([
            'tag'      => new TagResource($tag),
            'products' => ProductResource::collection($products)->response()->getData(true),
        ]);
    }
}
