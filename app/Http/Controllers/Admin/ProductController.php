<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SyncWoohooProductDetailsJob;
use App\Jobs\SyncWoohooProductsJob;
use App\Models\Category;
use App\Models\Product;
use App\Models\Tag;
use App\Services\Catalog\ProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $query = Product::with('category')->orderBy('category_id')->orderBy('name');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('external_id', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        $products = $query->paginate(20)->withQueryString();

        $categories = Category::orderBy('name')->get(['id', 'name']);

        return view('admin.products.index', compact('products', 'categories'));
    }

    public function show(Product $product): View
    {
        $product->loadMissing(['category', 'tags']);

        return view('admin.products.show', compact('product'));
    }

    public function edit(Product $product): View
    {
        $product->loadMissing('tags');
        $tags = Tag::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(['id', 'name']);

        return view('admin.products.edit', compact('product', 'tags'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'is_featured' => 'boolean',
            'is_trending' => 'boolean',
            'is_active' => 'boolean',
            'total_sales' => 'nullable|integer|min:0',
            'popularity_score' => 'nullable|integer|min:0',
            'deal_price' => 'nullable|numeric|min:0',
            'deal_start' => 'nullable|date',
            'deal_end' => 'nullable|date|after_or_equal:deal_start',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'exists:tags,id',
        ]);

        $product->update([
            'is_featured' => $request->boolean('is_featured'),
            'is_trending' => $request->boolean('is_trending'),
            'is_active' => $request->boolean('is_active'),
            'total_sales' => (int) ($validated['total_sales'] ?? 0),
            'popularity_score' => (int) ($validated['popularity_score'] ?? 0),
            'deal_price' => $validated['deal_price'] ?? null,
            'deal_start' => $validated['deal_start'] ?? null,
            'deal_end' => $validated['deal_end'] ?? null,
        ]);

        $product->tags()->sync($request->input('tag_ids', []));

        ProductService::clearProductListCache();

        return redirect()->route('admin.products.show', $product)->with('success', 'Product updated.');
    }

    public function sync(Request $request): RedirectResponse
    {
        $clearToken = $request->boolean('clear_token');
        SyncWoohooProductsJob::dispatch($clearToken)->onConnection('redis');

        return redirect()->route('admin.products.index')
            ->with('success', 'Product list sync has been queued. Monitor progress in Horizon.');
    }

    public function syncDetails(Request $request): RedirectResponse
    {
        $clearToken = $request->boolean('clear_token');
        SyncWoohooProductDetailsJob::dispatch($clearToken)->onConnection('redis');

        return redirect()->route('admin.products.index')
            ->with('success', 'Product details sync has been queued. Monitor progress in Horizon.');
    }
}
