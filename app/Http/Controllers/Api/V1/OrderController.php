<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\Controller;
use App\Models\Order;
use App\Models\Voucher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $orders = $request->user()
            ->orders()
            ->with('voucher.brand')
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return $this->success($orders);
    }

    public function show(Request $request, Order $order): JsonResponse
    {
        if ($order->user_id !== $request->user()->id) {
            return $this->error('Order not found', 404);
        }

        $order->load('voucher.brand');

        return $this->success($order);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'voucher_id' => 'required|exists:vouchers,id',
            'amount' => 'required|numeric|min:0',
            'quantity' => 'sometimes|integer|min:1|max:10',
            'recipient_email' => 'sometimes|nullable|email',
            'message' => 'sometimes|nullable|string|max:500',
        ]);

        $voucher = Voucher::findOrFail($validated['voucher_id']);
        if (! $voucher->is_active) {
            return $this->error('Voucher is not available', 422);
        }

        $order = $request->user()->orders()->create([
            'voucher_id' => $voucher->id,
            'amount' => $validated['amount'],
            'quantity' => $validated['quantity'] ?? 1,
            'status' => 'pending',
            'recipient_email' => $validated['recipient_email'] ?? null,
            'message' => $validated['message'] ?? null,
        ]);

        $order->load('voucher.brand');

        return $this->success($order, 'Order created', 201);
    }
}
