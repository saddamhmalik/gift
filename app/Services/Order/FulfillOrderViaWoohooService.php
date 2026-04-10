<?php

namespace App\Services\Order;

use App\Jobs\PollWoohooOrderStatusJob;
use App\Models\Order;
use App\Services\Woohoo\WoohooOrderService;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class FulfillOrderViaWoohooService
{
    public function __construct(
        protected WoohooOrderService $woohooOrderService
    ) {}

    /**
     * Create Woohoo order (SVC) and start status polling for async orders.
     * Call this after payment success from your payment gateway.
     *
     * syncOnly is derived automatically from total line quantity:
     *   - qty ≤ 4  → syncOnly = true  (Woohoo activates cards synchronously, returns 201)
     *   - qty ≥ 5  → syncOnly = false (async, Woohoo returns 202; poll Order Status API until
     *                COMPLETE, then call Activated Cards API to retrieve card details)
     *
     * @param  array{email?: string, telephone?: string, name?: string, line1?: string, city?: string, state?: string, postalCode?: string, country?: string}  $billing
     * @param  array<string, string>  $address
     * @param  bool|null  $syncOnly  null = auto-derive from quantity (recommended); pass bool to override
     * @return array{status: int, refno: string, woohoo_order_id?: string, sync: bool, poll_dispatched: bool}
     */
    public function fulfill(Order $order, array $billing = [], array $address = [], ?bool $syncOnly = null): array
    {
        $order->loadMissing(['items.product']);
        if ($order->items->isEmpty()) {
            throw new InvalidArgumentException('Order has no items.');
        }

        if ($syncOnly === null) {
            $totalQty = (int) $order->items->sum('quantity');
            $syncOnly = $totalQty <= 4;
            Log::info('Woohoo fulfill: syncOnly derived from quantity', [
                'order_id'  => $order->id,
                'total_qty' => $totalQty,
                'sync_only' => $syncOnly,
            ]);
        }

        $billing = $this->resolveBilling($order, $billing);
        if (empty($address)) {
            $address = $this->addressFromBilling($billing);
        }

        $result = $this->woohooOrderService->createOrder($order, $billing, $address, $syncOnly);
        $pollDispatched = false;

        // 202 = Woohoo accepted async (qty ≥ 5 or syncOnly forced false).
        // Poll Order Status API (by refno) until COMPLETE, then fetch Activated Cards.
        // refno is always stored before the API call, so polling can always recover even
        // if woohoo_order_id is missing from the 202 response.
        if ($result['status'] === 202) {
            PollWoohooOrderStatusJob::dispatchAfterAsyncOrder($order);
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

        // For deliveryMode=API, Woohoo only requires email (telephone is optional).
        // The payload builder applies E164 sanitization and omits telephone if invalid.
        if (empty($email)) {
            throw new InvalidArgumentException('Billing email is required. Set on order or ensure user has an email.');
        }

        // Build the resolved array first, then merge only non-empty $billing overrides.
        // This prevents an empty $billing['telephone'] from wiping a valid resolved value.
        $resolved = [
            'email'     => $email,
            'telephone' => $telephone ?: '',
            'name'      => $name ?: 'Customer',
            'firstname' => $billing['firstname'] ?? $user?->first_name ?? explode(' ', $name, 2)[0] ?? 'Customer',
            'line1'     => $billing['line1'] ?? 'N/A',
            'city'      => $billing['city'] ?? 'N/A',
            'state'     => $billing['state'] ?? '',
            'postalCode'=> $billing['postalCode'] ?? '',
            'country'   => $billing['country'] ?? 'IN',
        ];

        // Merge only billing fields that are non-empty so they don't clobber resolved values
        foreach ($billing as $k => $v) {
            if ($v !== null && $v !== '') {
                $resolved[$k] = $v;
            }
        }

        // Ensure email is always the correctly resolved value (never overwritten by an empty billing entry)
        $resolved['email'] = $email;
        $resolved['telephone'] = $telephone ?: '';

        return $resolved;
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
