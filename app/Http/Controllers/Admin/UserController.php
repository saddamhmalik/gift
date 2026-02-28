<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $status = $request->get('status');

        $users = User::query()
            ->when($search, fn ($q) => $q->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name',  'like', "%{$search}%")
                  ->orWhere('phone',      'like', "%{$search}%");
            }))
            ->withCount('orders')
            ->withSum('orders', 'total_amount')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $totalUsers    = User::count();
        $newThisMonth  = User::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        $withOrders    = User::has('orders')->count();

        return view('admin.users.index', compact('users', 'search', 'totalUsers', 'newThisMonth', 'withOrders'));
    }

    public function show(User $user)
    {
        $orders = $user->orders()
            ->with('items.product')
            ->latest()
            ->paginate(10);

        $stats = [
            'total_orders'    => $user->orders()->count(),
            'completed_orders'=> $user->orders()->where('status', Order::STATUS_COMPLETED)->count(),
            'pending_orders'  => $user->orders()->where('status', Order::STATUS_PENDING)->count(),
            'total_spent'     => $user->orders()->where('status', Order::STATUS_COMPLETED)->sum('total_amount'),
        ];

        return view('admin.users.show', compact('user', 'orders', 'stats'));
    }
}
