<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SyncWoohooProductDetailsJob;
use App\Jobs\SyncWoohooProductsJob;
use App\Models\Category;
use App\Models\Product;
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
        $product->loadMissing('category');

        return view('admin.products.show', compact('product'));
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
