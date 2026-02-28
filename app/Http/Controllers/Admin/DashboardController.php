<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'categoryCount'   => Category::count(),
            'productCount'    => Product::count(),
            'userCount'       => User::count(),
            'orderCount'      => Order::count(),
            'pendingOrders'   => Order::where('status', Order::STATUS_PENDING)->count(),
            'completedOrders' => Order::where('status', Order::STATUS_COMPLETED)->count(),
            'totalRevenue'    => Order::where('status', Order::STATUS_COMPLETED)->sum('total_amount'),
        ]);
    }
}
