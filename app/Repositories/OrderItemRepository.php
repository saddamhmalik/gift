<?php

namespace App\Repositories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;

class OrderItemRepository
{
    public function __construct(
        protected OrderItem $model
    ) {}

    public function find(int $id): ?OrderItem
    {
        return $this->model->find($id);
    }

    public function getByOrder(Order $order): ?OrderItem
    {
        return $this->model->where('order_id', $order->id)->first();
    }

    public function create(Order $order, Product $product, int $quantity, float $unitPrice, ?string $selectedDenomination = null): OrderItem
    {
        $totalPrice = round($unitPrice * $quantity, 2);
        return $this->model->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_price' => $totalPrice,
            'selected_denomination' => $selectedDenomination,
        ]);
    }

    public function updateQuantityAndPrice(OrderItem $item, int $quantity, float $unitPrice, ?string $selectedDenomination = null): OrderItem
    {
        $totalPrice = round($unitPrice * $quantity, 2);
        $item->update([
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_price' => $totalPrice,
            'selected_denomination' => $selectedDenomination,
        ]);
        return $item->fresh();
    }

    public function delete(OrderItem $item): bool
    {
        return $item->delete();
    }

    public function deleteByOrder(Order $order): void
    {
        $this->model->where('order_id', $order->id)->delete();
    }
}
