<?php

namespace App\Services\Order;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Repositories\OrderItemRepository;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class OrderService
{
    public function __construct(
        protected OrderRepository $orderRepository,
        protected OrderItemRepository $orderItemRepository,
        protected ProductRepository $productRepository
    ) {}

    /**
     * Create or fetch pending order (one product per order).
     */
    public function createOrFetch(?string $orderToken = null, ?User $user = null): Order
    {
        if ($user) {
            $order = $this->orderRepository->findPendingByUser($user);
            if ($order) {
                // Backfill order_token for legacy orders that were created without one
                if (! $order->order_token) {
                    $order->update(['order_token' => (string) Str::uuid()]);
                }
                return $order;
            }
            return $this->orderRepository->createForUser($user);
        }

        if ($orderToken) {
            $order = $this->orderRepository->findByToken($orderToken);
            if ($order) {
                return $order;
            }
        }

        return $this->orderRepository->createForGuest();
    }

    /**
     * Validate and resolve unit price from product.
     * RANGE (price_type): any value between min_price and max_price.
     * SLAB (price_type): only values in denominations (priceSlabs).
     */
    public function resolveUnitPrice(Product $product, ?float $unitPrice = null, ?string $selectedDenomination = null): float
    {
        if ($unitPrice !== null) {
            $this->validatePrice($product, (float) $unitPrice);
            return round((float) $unitPrice, 2);
        }

        if ($selectedDenomination !== null && $selectedDenomination !== '') {
            $slabs = $this->getPriceSlabs($product);
            if (is_array($slabs)) {
                $value = $this->parseDenominationValue($selectedDenomination, $slabs);
                if ($value !== null) {
                    return round($value, 2);
                }
            }
            throw new InvalidArgumentException('Selected denomination is not valid for this product.');
        }

        if ($product->isOnDeal() && $product->deal_price) {
            $dealPrice = (float) $product->deal_price;
            $this->validatePrice($product, $dealPrice);
            return round($dealPrice, 2);
        }

        $priceType = strtoupper((string) ($product->price_type ?? ''));
        $min = $product->min_price ? (float) $product->min_price : null;
        $max = $product->max_price ? (float) $product->max_price : null;
        $slabs = $this->getPriceSlabs($product);

        // For any product with denominations (SLAB or RANGE), default to the first denomination
        if (is_array($slabs) && count($slabs) > 0) {
            $first = $this->parseDenominationValue((string) $slabs[0], $slabs);
            if ($first !== null) {
                return round($first, 2);
            }
            throw new InvalidArgumentException('Provide unit_price or selected_denomination from allowed values: ' . implode(', ', $slabs));
        }

        // RANGE without denominations: default to min_price
        if ($min !== null) {
            return round($min, 2);
        }
        if ($max !== null) {
            return round($max, 2);
        }

        throw new InvalidArgumentException('Product has no price. Provide unit_price or selected_denomination.');
    }

    /**
     * Set or update the single product on the order (replaces any existing item).
     *
     * @param  array{order_mode?:string, delivery_mode?:string, gift_recipient_name?:string, gift_recipient_email?:string, gift_recipient_phone?:string, gift_message?:string}  $giftFields
     */
    public function setOrderProduct(
        Order $order,
        int $productId,
        int $quantity,
        ?float $unitPrice = null,
        ?string $selectedDenomination = null,
        array $giftFields = []
    ): Order {
        $product = $this->productRepository->find($productId);
        if (! $product || ! $product->is_active) {
            abort(404, 'Product not found or unavailable');
        }

        $unitPrice = $this->resolveUnitPrice($product, $unitPrice, $selectedDenomination);

        if ($quantity < 1 || $quantity > 99) {
            throw new InvalidArgumentException('Quantity must be between 1 and 99.');
        }

        return DB::transaction(function () use ($order, $product, $quantity, $unitPrice, $selectedDenomination, $giftFields) {
            $order = $this->orderRepository->lockForUpdate($order);

            // Persist gift / delivery fields on the order itself
            if (! empty($giftFields)) {
                $updateData = array_filter([
                    'order_mode'           => $giftFields['order_mode'] ?? null,
                    'delivery_mode'        => $giftFields['delivery_mode'] ?? null,
                    'gift_recipient_name'  => $giftFields['gift_recipient_name'] ?? null,
                    'gift_recipient_email' => $giftFields['gift_recipient_email'] ?? null,
                    'gift_recipient_phone' => $giftFields['gift_recipient_phone'] ?? null,
                    'gift_message'         => $giftFields['gift_message'] ?? null,
                ], fn ($v) => $v !== null);
                if (! empty($updateData)) {
                    $order->update($updateData);
                }
            }

            $existing = $this->orderItemRepository->getByOrder($order);
            if ($existing) {
                $this->orderItemRepository->updateQuantityAndPrice($existing, $quantity, $unitPrice, $selectedDenomination);
            } else {
                $this->orderItemRepository->create($order, $product, $quantity, $unitPrice, $selectedDenomination);
            }

            $this->refreshOrderTotal($order);
            return $order->fresh(['items.product']);
        });
    }

    /**
     * Update quantity/price of the order's single item (null = keep current).
     */
    public function updateOrderItem(Order $order, ?int $quantity = null, ?float $unitPrice = null, ?string $selectedDenomination = null): Order
    {
        $item = $this->orderItemRepository->getByOrder($order);
        if (! $item) {
            abort(404, 'Order has no item. Add a product first.');
        }

        $product = $item->product;
        $quantity = $quantity ?? $item->quantity;
        $unitPrice = ($unitPrice !== null || $selectedDenomination !== null)
            ? $this->resolveUnitPrice($product, $unitPrice, $selectedDenomination)
            : (float) $item->unit_price;

        if ($quantity < 1 || $quantity > 99) {
            throw new InvalidArgumentException('Quantity must be between 1 and 99.');
        }

        DB::transaction(function () use ($item, $order, $quantity, $unitPrice, $selectedDenomination) {
            $this->orderItemRepository->updateQuantityAndPrice($item, $quantity, $unitPrice, $selectedDenomination);
            $this->refreshOrderTotal($order);
        });

        return $order->fresh(['items.product']);
    }

    /**
     * Remove the product from the order.
     */
    public function clearOrder(Order $order): Order
    {
        DB::transaction(function () use ($order) {
            $this->orderItemRepository->deleteByOrder($order);
            $order->update(['total_amount' => 0, 'currency_code' => null]);
        });
        return $order->fresh(['items']);
    }

    public function getOrder(Order $order): Order
    {
        return $order->load(['items.product']);
    }

    public function resolveOrder(?string $orderToken, ?User $user): ?Order
    {
        if ($user) {
            return $this->orderRepository->findPendingByUser($user);
        }
        if ($orderToken) {
            return $this->orderRepository->findByToken($orderToken);
        }
        return null;
    }

    /**
     * Validate price against product pricing rules.
     *
     * Rules (Woohoo enforces all of these):
     *  - SLAB                        → price must be exactly one of the denominations
     *  - RANGE with denominations    → price must be exactly one of the denominations
     *                                  (Woohoo rejects any value not in the list, even within range)
     *  - RANGE without denominations → any value within [min_price, max_price]
     */
    protected function validatePrice(Product $product, float $price): void
    {
        $slabs = $this->getPriceSlabs($product);

        // When denominations are defined they are the only allowed values — applies to both SLAB and RANGE
        if (is_array($slabs) && count($slabs) > 0) {
            $allowed = array_values(array_filter(array_map(
                fn ($d) => is_numeric($d) ? (float) $d : null,
                $slabs
            )));
            $rounded = round($price, 2);
            foreach ($allowed as $a) {
                if (abs(round($a, 2) - $rounded) < 0.01) {
                    return;
                }
            }
            throw new InvalidArgumentException(
                'Price must be one of the allowed denominations: ' . implode(', ', array_map('strval', $allowed))
            );
        }

        // No denominations: validate within min/max range
        $min = $product->min_price ? (float) $product->min_price : null;
        $max = $product->max_price ? (float) $product->max_price : null;
        if ($min !== null && $price < $min) {
            throw new InvalidArgumentException("Price must be at least {$min}.");
        }
        if ($max !== null && $price > $max) {
            throw new InvalidArgumentException("Price must be at most {$max}.");
        }
    }

    /**
     * Price slabs for SLAB type (stored in denominations column).
     *
     * @return array<int|float|string>|null
     */
    protected function getPriceSlabs(Product $product): ?array
    {
        $denominations = $product->denominations;
        return is_array($denominations) ? $denominations : null;
    }

    protected function parseDenominationValue(string $selected, array $denominations): ?float
    {
        $selectedNum = is_numeric($selected) ? (float) $selected : null;
        if ($selectedNum === null) {
            return null;
        }
        foreach ($denominations as $d) {
            $val = is_numeric($d) ? (float) $d : null;
            if ($val !== null && abs($val - $selectedNum) < 0.01) {
                return $val;
            }
        }
        return null;
    }

    protected function refreshOrderTotal(Order $order): void
    {
        $total = $order->items()->sum('total_price');
        $currency = $order->items()->first()?->product?->currency_code;
        $order->update(['total_amount' => $total, 'currency_code' => $currency]);
    }
}
