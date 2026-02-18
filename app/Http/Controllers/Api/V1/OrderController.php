<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\Controller;
use App\Http\Resources\V1\OrderResource;
use App\Repositories\OrderRepository;
use App\Services\Order\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService,
        protected OrderRepository $orderRepository
    ) {}

    /**
     * POST /api/v1/order - Create or fetch order.
     */
    public function store(Request $request): JsonResponse
    {
        $orderToken = $request->input('order_token');
        $user = $request->user();

        $order = $this->orderService->createOrFetch($orderToken, $user);
        $order = $this->orderService->getOrder($order);

        return $this->success(new OrderResource($order), 'Order ready', 201);
    }

    /**
     * GET /api/v1/order - Get current (pending) order.
     */
    public function show(Request $request): JsonResponse
    {
        $orderToken = $request->input('order_token') ?? $request->header('X-Order-Token');
        $user = $request->user();

        $order = $this->orderService->resolveOrder($orderToken, $user);
        if (! $order) {
            return $this->error('Order not found. Create an order first with POST /order', 404);
        }

        $order = $this->orderService->getOrder($order);

        return $this->success(new OrderResource($order));
    }

    /**
     * GET /api/v1/order/{order} - Get order by ID (user's own orders only).
     */
    public function showById(Request $request, int $order): JsonResponse
    {
        $orderModel = $this->orderRepository->findByIdAndUser($order, $request->user());
        if (! $orderModel) {
            return $this->error('Order not found', 404);
        }
        $orderModel = $this->orderService->getOrder($orderModel);
        return $this->success(new OrderResource($orderModel));
    }

    /**
     * POST /api/v1/order/item - Set the single product on the order (price from min/max or denominations).
     */
    public function setItem(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'quantity' => 'nullable|integer|min:1|max:99',
            'unit_price' => 'nullable|numeric|min:0',
            'selected_denomination' => 'nullable|string|max:100',
            'order_token' => 'nullable|string',
        ]);

        $orderToken = $request->input('order_token') ?? $request->header('X-Order-Token');
        $user = $request->user();

        $order = $this->orderService->resolveOrder($orderToken, $user);
        if (! $order) {
            return $this->error('Order not found. Create an order first with POST /order', 404);
        }

        try {
            $order = $this->orderService->setOrderProduct(
                $order,
                (int) $request->product_id,
                (int) ($request->quantity ?? 1),
                $request->has('unit_price') ? (float) $request->unit_price : null,
                $request->input('selected_denomination')
            );
        } catch (InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 422);
        }

        return $this->success(new OrderResource($order), 'Order updated');
    }

    /**
     * PUT /api/v1/order/item - Update quantity or price of the order's product.
     */
    public function updateItem(Request $request): JsonResponse
    {
        $request->validate([
            'quantity' => 'nullable|integer|min:1|max:99',
            'unit_price' => 'nullable|numeric|min:0',
            'selected_denomination' => 'nullable|string|max:100',
            'order_token' => 'nullable|string',
        ]);

        $orderToken = $request->input('order_token') ?? $request->header('X-Order-Token');
        $user = $request->user();

        $order = $this->orderService->resolveOrder($orderToken, $user);
        if (! $order) {
            return $this->error('Order not found', 404);
        }

        try {
            $order = $this->orderService->updateOrderItem(
                $order,
                $request->has('quantity') ? (int) $request->quantity : null,
                $request->has('unit_price') ? (float) $request->unit_price : null,
                $request->input('selected_denomination')
            );
        } catch (InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 422);
        }

        return $this->success(new OrderResource($order));
    }

    /**
     * DELETE /api/v1/order/item - Remove product from order.
     */
    public function clearItem(Request $request): JsonResponse
    {
        $orderToken = $request->input('order_token') ?? $request->header('X-Order-Token');
        $user = $request->user();

        $order = $this->orderService->resolveOrder($orderToken, $user);
        if (! $order) {
            return $this->error('Order not found', 404);
        }

        $order = $this->orderService->clearOrder($order);

        return $this->success(new OrderResource($order), 'Order cleared');
    }
}
