<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\RefundOrderJob;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $search   = $request->get('search');
        $status   = $request->get('status');
        $refund   = $request->get('refund');

        $orders = Order::query()
            ->with(['user', 'items.product'])
            ->when($search, fn ($q) => $q->where(function ($q) use ($search) {
                $q->where('id', $search)
                  ->orWhere('woohoo_refno', 'like', "%{$search}%")
                  ->orWhere('woohoo_order_id', 'like', "%{$search}%")
                  ->orWhere('payu_mihpayid', 'like', "%{$search}%")
                  ->orWhereHas('user', fn ($u) => $u
                      ->where('email', 'like', "%{$search}%")
                      ->orWhere('first_name', 'like', "%{$search}%")
                  );
            }))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($refund === 'failed',  fn ($q) => $q->where('refund_status', Order::REFUND_STATUS_FAILED))
            ->when($refund === 'pending', fn ($q) => $q->where('refund_status', Order::REFUND_STATUS_PENDING))
            ->when($refund === 'needed',  fn ($q) => $q->where('status', Order::STATUS_CANCELLED)
                ->whereNotIn('refund_status', [Order::REFUND_STATUS_REFUNDED])
                ->whereNotNull('payu_mihpayid'))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $stats = [
            'total'           => Order::count(),
            'completed'       => Order::where('status', Order::STATUS_COMPLETED)->count(),
            'pending'         => Order::where('status', Order::STATUS_PENDING)->count(),
            'cancelled'       => Order::where('status', Order::STATUS_CANCELLED)->count(),
            'refund_failed'   => Order::where('refund_status', Order::REFUND_STATUS_FAILED)->count(),
            'refund_pending'  => Order::where('refund_status', Order::REFUND_STATUS_PENDING)->count(),
            'refunded'        => Order::where('refund_status', Order::REFUND_STATUS_REFUNDED)->count(),
        ];

        return view('admin.orders.index', compact('orders', 'search', 'status', 'refund', 'stats'));
    }

    public function show(Order $order)
    {
        $order->load(['user', 'items.product']);
        return view('admin.orders.show', compact('order'));
    }

    public function retryRefund(Order $order): RedirectResponse
    {
        // Block refund on successfully fulfilled orders
        if ($order->status === Order::STATUS_COMPLETED && $order->delivery_status === 'fulfilled') {
            return back()->with('error', 'Cannot refund: this order was fulfilled successfully.');
        }

        if (empty($order->payu_mihpayid)) {
            return back()->with('error', 'Cannot refund: no PayU transaction ID stored for this order.');
        }

        if ($order->refund_status === Order::REFUND_STATUS_REFUNDED) {
            return back()->with('error', 'This order has already been refunded.');
        }

        if ($order->refund_status === Order::REFUND_STATUS_PENDING) {
            return back()->with('error', 'A refund is already in progress for this order.');
        }

        // Reset so the job can proceed (idempotency guard checks for pending/refunded)
        $order->update([
            'refund_status' => null,
            'refund_reason' => null,
        ]);

        RefundOrderJob::dispatch($order, 'Manual refund initiated by admin');

        return back()->with('success', 'Refund job dispatched. Status will update shortly.');
    }
}
