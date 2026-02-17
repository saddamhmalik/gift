<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $categoryCount = Category::count();
        $productCount = Product::count();

        return view('admin.dashboard', [
            'categoryCount' => $categoryCount,
            'productCount' => $productCount,
        ]);
    }
}
