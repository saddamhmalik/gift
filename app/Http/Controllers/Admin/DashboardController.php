<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        // ── Headline stats ────────────────────────────────────────────────
        $categoryCount   = Category::count();
        $productCount    = Product::count();
        $userCount       = User::count();
        $orderCount      = Order::count();
        $pendingOrders   = Order::where('status', Order::STATUS_PENDING)->count();
        $completedOrders = Order::where('status', Order::STATUS_COMPLETED)->count();
        $cancelledOrders = Order::where('status', Order::STATUS_CANCELLED)->count();
        $totalRevenue    = Order::where('status', Order::STATUS_COMPLETED)->sum('total_amount');

        // ── Revenue by day – last 30 days ─────────────────────────────────
        $revenueRaw = Order::query()
            ->select(DB::raw('DATE(created_at) as day'), DB::raw('SUM(total_amount) as total'))
            ->where('status', Order::STATUS_COMPLETED)
            ->where('created_at', '>=', now()->subDays(29)->startOfDay())
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day');

        $revenueDays   = [];
        $revenueValues = [];
        for ($i = 29; $i >= 0; $i--) {
            $day = now()->subDays($i)->format('Y-m-d');
            $revenueDays[]   = now()->subDays($i)->format('d M');
            $revenueValues[] = (float) ($revenueRaw[$day] ?? 0);
        }

        // ── Orders by day – last 30 days ──────────────────────────────────
        $ordersRaw = Order::query()
            ->select(DB::raw('DATE(created_at) as day'), DB::raw('COUNT(*) as total'))
            ->where('created_at', '>=', now()->subDays(29)->startOfDay())
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day');

        $ordersValues = [];
        for ($i = 29; $i >= 0; $i--) {
            $day = now()->subDays($i)->format('Y-m-d');
            $ordersValues[] = (int) ($ordersRaw[$day] ?? 0);
        }

        // ── New users by day – last 30 days ───────────────────────────────
        $usersRaw = User::query()
            ->select(DB::raw('DATE(created_at) as day'), DB::raw('COUNT(*) as total'))
            ->where('created_at', '>=', now()->subDays(29)->startOfDay())
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day');

        $usersValues = [];
        for ($i = 29; $i >= 0; $i--) {
            $day = now()->subDays($i)->format('Y-m-d');
            $usersValues[] = (int) ($usersRaw[$day] ?? 0);
        }

        // ── Top 6 categories by revenue ───────────────────────────────────
        $topCategories = DB::table('order_items')
            ->join('orders',    'orders.id',    '=', 'order_items.order_id')
            ->join('products',  'products.id',  '=', 'order_items.product_id')
            ->join('categories','categories.id','=', 'products.category_id')
            ->where('orders.status', Order::STATUS_COMPLETED)
            ->select('categories.name', DB::raw('SUM(order_items.total_price) as revenue'))
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('revenue')
            ->limit(6)
            ->get();

        // ── Revenue this month vs last month ─────────────────────────────
        $thisMonth = Order::where('status', Order::STATUS_COMPLETED)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total_amount');

        $lastMonth = Order::where('status', Order::STATUS_COMPLETED)
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->sum('total_amount');

        $revenueGrowth = $lastMonth > 0
            ? round((($thisMonth - $lastMonth) / $lastMonth) * 100, 1)
            : null;

        // ── Recent orders ─────────────────────────────────────────────────
        $recentOrders = Order::with('user')
            ->latest()
            ->limit(8)
            ->get();

        return view('admin.dashboard', compact(
            'categoryCount', 'productCount', 'userCount',
            'orderCount', 'pendingOrders', 'completedOrders', 'cancelledOrders', 'totalRevenue',
            'revenueDays', 'revenueValues', 'ordersValues', 'usersValues',
            'topCategories', 'thisMonth', 'lastMonth', 'revenueGrowth',
            'recentOrders',
        ));
    }
}
