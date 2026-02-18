<?php

namespace App\Services\Order;

use App\Jobs\PollWoohooOrderStatusJob;
use App\Models\Order;
use App\Services\Woohoo\WoohooOrderService;
use InvalidArgumentException;

class FulfillOrderViaWoohooService
{
    public function __construct(
        protected WoohooOrderService $woohooOrderService
    ) {}

    /**
     * Create Woohoo order (SVC) and optionally start status polling for async.
     * Call this after payment success from your payment gateway.
     *
     * @param  array{email?: string, telephone?: string, name?: string, line1?: string, city?: string, state?: string, postalCode?: string, country?: string}  $billing  Billing (email, telephone mandatory). If omitted, uses order's stored billing_*.
     * @param  array<string, string>  $address  Optional address; falls back to billing.
     * @return array{status: int, refno: string, woohoo_order_id?: string, sync: bool, poll_dispatched: bool}
     */
    public function fulfill(Order $order, array $billing = [], array $address = [], bool $syncOnly = false): array
    {
        $order->loadMissing(['items.product']);
        if ($order->items->isEmpty()) {
            throw new InvalidArgumentException('Order has no items.');
        }

        $billing = $this->resolveBilling($order, $billing);
        if (empty($address)) {
            $address = $this->addressFromBilling($billing);
        }

        $result = $this->woohooOrderService->createOrder($order, $billing, $address, $syncOnly);
        $pollDispatched = false;
        if ($result['status'] === 202 && ! $result['sync']) {
            PollWoohooOrderStatusJob::dispatch($order);
            $pollDispatched = true;
        }

        return array_merge($result, ['poll_dispatched' => $pollDispatched]);
    }

    /**
     * @param  array<string, string>  $billing
     * @return array{email: string, telephone: string, name?: string, ...}
     */
    protected function resolveBilling(Order $order, array $billing): array
    {
        $order->loadMissing('user');
        $user = $order->user;

        $email = $billing['email'] ?? $order->billing_email ?? $user?->email;
        $telephone = $billing['telephone'] ?? $order->billing_telephone ?? $user?->phone;
        $name = $billing['name'] ?? $billing['firstname'] ?? $order->billing_name
            ?? ($user ? trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) : null)
            ?? $user?->name ?? 'Customer';

        if (empty($email) || empty($telephone)) {
            throw new InvalidArgumentException('Billing email and telephone are required. Set on order, pass in billing, or ensure user has them from signup.');
        }

        return array_merge([
            'email' => $email,
            'telephone' => $telephone,
            'name' => $name ?: 'Customer',
            'firstname' => $billing['firstname'] ?? $user?->first_name ?? explode(' ', $name, 2)[0] ?? 'Customer',
            'line1' => $billing['line1'] ?? 'N/A',
            'city' => $billing['city'] ?? 'N/A',
            'state' => $billing['state'] ?? '',
            'postalCode' => $billing['postalCode'] ?? '',
            'country' => $billing['country'] ?? 'IN',
        ], $billing);
    }

    /**
     * @param  array<string, string>  $billing
     * @return array<string, string>
     */
    protected function addressFromBilling(array $billing): array
    {
        return [
            'email' => $billing['email'] ?? '',
            'telephone' => $billing['telephone'] ?? '',
            'name' => $billing['name'] ?? 'Customer',
            'line1' => $billing['line1'] ?? 'N/A',
            'city' => $billing['city'] ?? 'N/A',
            'state' => $billing['state'] ?? '',
            'postalCode' => $billing['postalCode'] ?? '',
            'country' => $billing['country'] ?? 'IN',
        ];
    }
}
