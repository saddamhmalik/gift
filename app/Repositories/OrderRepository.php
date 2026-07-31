<?php

namespace App\Repositories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Str;

class OrderRepository
{
    public function __construct(
        protected Order $model
    ) {}

    public function find(int $id): ?Order
    {
        return $this->model->find($id);
    }

    public function findByIdAndUser(int $id, User $user): ?Order
    {
        return $this->model->where('id', $id)->where('user_id', $user->id)->first();
    }

    public function allForUser(User $user, int $perPage = 15): \Illuminate\Pagination\LengthAwarePaginator
    {
        return $this->model
            ->with(['items.product'])
            ->where('user_id', $user->id)
            ->latest()
            ->paginate($perPage);
    }

    public function findByToken(string $orderToken): ?Order
    {
        return $this->model
            ->pending()
            ->where('order_token', $orderToken)
            ->first();
    }

    public function findPendingByUser(User $user): ?Order
    {
        return $this->model
            ->pending()
            ->where('user_id', $user->id)
            ->whereNull('payu_mihpayid')  // payment not completed
            ->whereNull('woohoo_refno')   // Woohoo order not attempted
            ->latest('id')
            ->first();
    }

    public function createForGuest(): Order
    {
        return $this->model->create([
            'order_token' => (string) Str::uuid(),
            'status' => Order::STATUS_PENDING,
        ]);
    }

    public function createForUser(User $user): Order
    {
        return $this->model->create([
            'user_id'     => $user->id,
            'order_token' => (string) Str::uuid(),
            'status'      => Order::STATUS_PENDING,
        ]);
    }

    public function lockForUpdate(Order $order): Order
    {
        return $this->model->where('id', $order->id)->lockForUpdate()->first();
    }
}
