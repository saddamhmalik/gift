<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\WoohooProductDetailService;
use App\Services\WoohooProductSyncService;
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

        $categories = \App\Models\Category::orderBy('name')->get(['id', 'name']);

        return view('admin.products.index', compact('products', 'categories'));
    }

    public function show(Product $product): View
    {
        $product->load('category');

        return view('admin.products.show', compact('product'));
    }

    public function sync(WoohooProductSyncService $sync): RedirectResponse
    {
        $result = $sync->sync(false);

        if ($result['success']) {
            return redirect()->route('admin.products.index')
                ->with('success', "Synced {$result['synced']} products from Woohoo.");
        }

        $message = $result['synced'] > 0
            ? "Synced {$result['synced']} products. " . ($result['message'] ?? '')
            : ($result['message'] ?? 'Sync failed.');

        return redirect()->route('admin.products.index')
            ->with($result['synced'] > 0 ? 'success' : 'error', trim($message));
    }

    public function syncDetails(WoohooProductDetailService $sync): RedirectResponse
    {
        $result = $sync->syncAll(false);

        if ($result['synced'] > 0) {
            $msg = "Synced details for {$result['synced']} product(s).";
            if ($result['failed'] > 0) {
                $msg .= " {$result['failed']} failed.";
            }
            return redirect()->route('admin.products.index')->with('success', $msg);
        }

        return redirect()->route('admin.products.index')
            ->with('error', $result['failed'] > 0 ? 'No product details could be fetched.' : 'No products to sync. Sync product list first.');
    }
}
