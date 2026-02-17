<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Catalog\CategoryService;
use App\Services\Catalog\ProductService;
use Illuminate\Http\RedirectResponse;

class CacheController extends Controller
{
    public function clear(): RedirectResponse
    {
        CategoryService::clearCache();
        ProductService::clearProductListCache();

        return redirect()->back()->with('success', 'API cache cleared successfully.');
    }
}
