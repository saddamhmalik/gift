<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\Controller;
use App\Http\Resources\V1\OrderResource;
use App\Repositories\OrderRepository;
use App\Services\Order\OrderService;
use App\Services\Woohoo\WoohooActivatedCardsService;
use App\Services\Woohoo\WoohooOrderService;
use App\Services\Woohoo\WoohooResendService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use InvalidArgumentException;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService,
        protected OrderRepository $orderRepository,
        protected WoohooActivatedCardsService $activatedCardsService,
        protected WoohooOrderService $woohooOrderService,
        protected WoohooResendService $resendService,
    ) {}

    /**
     * GET /api/v1/orders - List all orders for authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $paginator = $this->orderRepository->allForUser($request->user(), 10);
        return $this->success(
            OrderResource::collection($paginator)->response()->getData(true)
        );
    }

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
            'product_id'             => 'required|integer|exists:products,id',
            'quantity'               => 'nullable|integer|min:1|max:99',
            'unit_price'             => 'nullable|numeric|min:0',
            'selected_denomination'  => 'nullable|string|max:100',
            'order_token'            => 'nullable|string',
            // Gift fields
            'order_mode'             => 'nullable|string|in:SELF,GIFT',
            'delivery_mode'          => 'nullable|string|in:API,EMAIL,SMS,ANY',
            'gift_recipient_name'    => 'nullable|string|max:100',
            'gift_recipient_email'   => 'nullable|email|max:191',
            'gift_recipient_phone'   => 'nullable|string|max:20',
            'gift_message'           => 'nullable|string|max:500',
        ]);

        $orderToken = $request->input('order_token') ?? $request->header('X-Order-Token');
        $user = $request->user();

        $order = $this->orderService->resolveOrder($orderToken, $user);
        if (! $order) {
            return $this->error('Order not found. Create an order first with POST /order', 404);
        }

        // Collect gift-related fields
        $giftFields = [];
        $orderMode = $request->input('order_mode', 'SELF');
        if ($orderMode === 'GIFT') {
            if (empty($request->input('gift_recipient_email')) && empty($request->input('gift_recipient_phone'))) {
                return $this->error('Gift orders require at least a recipient email or phone.', 422);
            }
        }
        $giftFields = [
            'order_mode'           => $orderMode,
            'delivery_mode'        => $orderMode === 'GIFT' ? ($request->input('delivery_mode', 'EMAIL')) : 'API',
            'gift_recipient_name'  => $request->input('gift_recipient_name'),
            'gift_recipient_email' => $request->input('gift_recipient_email'),
            'gift_recipient_phone' => $request->input('gift_recipient_phone'),
            'gift_message'         => $request->input('gift_message'),
        ];

        try {
            $order = $this->orderService->setOrderProduct(
                $order,
                (int) $request->product_id,
                (int) ($request->quantity ?? 1),
                $request->has('unit_price') ? (float) $request->unit_price : null,
                $request->input('selected_denomination'),
                $giftFields
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
     * GET /api/v1/order/{order}/cards
     *
     * Fetch activated card details for a completed order.
     * Tries the Woohoo Activated Cards API first (fresh, live data).
     * Falls back to the encrypted stored data if Woohoo is unavailable.
     * Returns the cards array directly — does NOT go through OrderResource,
     * so it works regardless of any server-side caching of old PHP bytecode.
     */
    public function fetchCards(Request $request, int $order): JsonResponse
    {
        $orderModel = $this->orderRepository->findByIdAndUser($order, $request->user());
        if (! $orderModel) {
            return $this->error('Order not found', 404);
        }

        $cards        = null;
        $deliveryMode = null;
        $delivery     = null;

        // ── 1. Try live Woohoo Activated Cards API ────────────────────────
        if ($orderModel->woohoo_order_id) {
            $result = $this->activatedCardsService->fetchAndNormalize($orderModel->woohoo_order_id);
            if ($result['success'] && ! empty($result['cards'])) {
                // Persist the fresh data so future loads are correct
                $this->woohooOrderService->storeCardDetailsEncrypted($orderModel, $result);
                $cards        = $result['cards'];
                $deliveryMode = $result['deliveryMode'] ?? null;
                $delivery     = $result['delivery']     ?? null;
            }
        }

        // ── 2. Fall back to stored encrypted data ─────────────────────────
        if ($cards === null && $orderModel->card_details_encrypted) {
            try {
                $raw     = Crypt::decryptString($orderModel->card_details_encrypted);
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) {
                    if (isset($decoded['cards'])) {
                        // New format: full Activated Cards API response
                        $cards        = $decoded['cards'];
                        $deliveryMode = $decoded['deliveryMode'] ?? null;
                        $delivery     = $decoded['delivery']     ?? null;
                    } else {
                        // Legacy format: plain array of card objects
                        $cards = $decoded;
                    }
                }
            } catch (\Throwable) {
                // Decryption failed
            }
        }

        if (empty($cards)) {
            return $this->error('Card details are not yet available. Please try again in a moment.', 404);
        }

        return $this->success([
            'cards'         => $cards,
            'delivery_mode' => $deliveryMode,
            'card_delivery' => $delivery,
        ]);
    }

    /**
     * POST /api/v1/order/{order}/resend
     *
     * Resend card details via Woohoo's Resend API.
     * Only applicable for EMAIL / SMS / ANY delivery orders.
     * Optionally accepts updated recipient contact information.
     */
    public function resend(Request $request, int $order): JsonResponse
    {
        $request->validate([
            'name'      => 'nullable|string|max:100',
            'email'     => 'nullable|email|max:191',
            'telephone' => 'nullable|string|max:20',
        ]);

        $orderModel = $this->orderRepository->findByIdAndUser($order, $request->user());
        if (! $orderModel) {
            return $this->error('Order not found', 404);
        }

        $deliveryMode = $orderModel->delivery_mode ?? 'API';
        if ($deliveryMode === 'API') {
            return $this->error('Resend is only available for EMAIL, SMS, or ANY delivery orders.', 422);
        }

        try {
            $result = $this->resendService->resend(
                $orderModel,
                array_filter($request->only(['name', 'email', 'telephone']), fn ($v) => $v !== null)
            );

            if ($result['success']) {
                return $this->success([], $result['message'] ?? 'Card details resent successfully.');
            }

            return $this->error($result['error'] ?? 'Failed to resend card details.', $result['status'] ?? 500);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 422);
        } catch (\Throwable $e) {
            return $this->error('Failed to resend: ' . $e->getMessage(), 500);
        }
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
