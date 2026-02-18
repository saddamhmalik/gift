<?php

namespace App\Services\Woohoo;

use App\Models\Order;
use InvalidArgumentException;

class WoohooOrderPayloadBuilder
{
    /**
     * Build payload for POST /rest/v3/orders.
     *
     * @param  array{email: string, telephone: string, name?: string, line1?: string, city?: string, state?: string, postalCode?: string, country?: string}  $billing
     * @param  array{email?: string, telephone?: string, name?: string, line1?: string, city?: string, state?: string, postalCode?: string, country?: string}  $address
     * @return array<string, mixed>
     */
    public function build(
        Order $order,
        string $refno,
        array $billing,
        array $address = [],
        bool $syncOnly = false
    ): array {
        $order->loadMissing(['items.product']);
        $items = $order->items;
        if ($items->isEmpty()) {
            throw new InvalidArgumentException('Order has no items.');
        }

        $products = [];
        $totalAmount = 0.0;
        foreach ($items as $item) {
            $product = $item->product;
            if (! $product || ! $product->external_id) {
                throw new InvalidArgumentException("Product id {$item->product_id} has no Woohoo external_id.");
            }
            $unitPrice = (float) $item->unit_price;
            $qty = (int) $item->quantity;
            $lineTotal = round($unitPrice * $qty, 2);
            $totalAmount += $lineTotal;
            $currencyNumeric = $this->getCurrencyNumericCode($product->currency_code ?? 'INR');
            $products[] = [
                'sku' => (string) $product->external_id,
                'price' => $unitPrice,
                'qty' => $qty,
                'currency' => $currencyNumeric,
            ];
        }
        $totalAmount = round($totalAmount, 2);

        $email = $billing['email'] ?? null;
        $telephone = $billing['telephone'] ?? null;
        if (empty($email) || empty($telephone)) {
            throw new InvalidArgumentException('Billing email and telephone are mandatory.');
        }

        $addressPayload = $this->buildAddressPayload($address, $billing);
        $billingPayload = $this->buildBillingPayload($billing, $address);

        $poNumber = (string) ($order->id ?? $refno);

        $payload = [
            'refno' => $refno,
            'deliveryMode' => 'API',
            'syncOnly' => $syncOnly,
            'products' => $products,
            'payments' => [
                [
                    'code' => 'svc',
                    'amount' => $totalAmount,
                    'poNumber' => $poNumber,
                    'mode' => 'ANY',
                ],
            ],
            'billing' => $billingPayload,
            'address' => $addressPayload,
        ];

        return $payload;
    }

    /**
     * @param  array<string, string>  $address
     * @param  array<string, string>  $billing
     * @return array<string, string>
     */
    protected function buildAddressPayload(array $address, array $billing): array
    {
        $firstname = $address['name'] ?? $address['firstname'] ?? $billing['name'] ?? $billing['firstname'] ?? 'Customer';
        $postcode = $address['postalCode'] ?? $address['postcode'] ?? $billing['postalCode'] ?? $billing['postcode'] ?? '000000';
        $postcode = strlen((string) $postcode) >= 3 ? (string) $postcode : '000000';
        return [
            'country' => $address['country'] ?? $billing['country'] ?? 'IN',
            'email' => $address['email'] ?? $billing['email'] ?? '',
            'firstname' => $firstname,
            'line1' => $address['line1'] ?? $billing['line1'] ?? 'N/A',
            'city' => $address['city'] ?? $billing['city'] ?? 'N/A',
            'region' => $address['region'] ?? $address['state'] ?? $billing['region'] ?? $billing['state'] ?? 'N/A',
            'postcode' => $postcode,
            'telephone' => $address['telephone'] ?? $billing['telephone'] ?? '',
        ];
    }

    protected function buildBillingPayload(array $billing, array $address): array
    {
        $firstname = $billing['name'] ?? $billing['firstname'] ?? 'Customer';
        $postcode = $billing['postalCode'] ?? $billing['postcode'] ?? $address['postalCode'] ?? $address['postcode'] ?? '000000';
        $postcode = strlen((string) $postcode) >= 3 ? (string) $postcode : '000000';
        return [
            'country' => $billing['country'] ?? $address['country'] ?? 'IN',
            'email' => $billing['email'] ?? '',
            'firstname' => $firstname,
            'telephone' => $billing['telephone'] ?? '',
            'line1' => $billing['line1'] ?? $address['line1'] ?? 'N/A',
            'city' => $billing['city'] ?? $address['city'] ?? 'N/A',
            'region' => $billing['region'] ?? $billing['state'] ?? $address['region'] ?? $address['state'] ?? 'N/A',
            'postcode' => $postcode,
        ];
    }

    /**
     * ISO 4217 numeric code (3-digit).
     * @see https://en.wikipedia.org/wiki/ISO_4217
     */
    protected function getCurrencyNumericCode(string $currencyCode): int
    {
        return match (strtoupper($currencyCode)) {
            'AED' => 784,  // United Arab Emirates dirham
            'AFN' => 971,  // Afghan afghani
            'ALL' => 8,    // Albanian lek
            'AMD' => 51,   // Armenian dram
            'AUD' => 36,   // Australian dollar
            'BHD' => 48,   // Bahraini dinar
            'BRL' => 986,  // Brazilian real
            'CAD' => 124,  // Canadian dollar
            'CHF' => 756,  // Swiss franc
            'CNY' => 156,  // Chinese yuan
            'EGP' => 818,  // Egyptian pound
            'EUR' => 978,  // Euro
            'GBP' => 826,  // Pound sterling
            'HKD' => 344,  // Hong Kong dollar
            'IDR' => 360,  // Indonesian rupiah
            'INR' => 356,  // Indian rupee
            'JPY' => 392,  // Japanese yen
            'KES' => 404,  // Kenyan shilling
            'KWD' => 414,  // Kuwaiti dinar
            'MYR' => 458,  // Malaysian ringgit
            'NGN' => 566,  // Nigerian naira
            'NZD' => 554,  // New Zealand dollar
            'OMR' => 512,  // Omani rial
            'PKR' => 586,  // Pakistani rupee
            'PHP' => 608,  // Philippine peso
            'PLN' => 985,  // Polish złoty
            'QAR' => 634,  // Qatari riyal
            'SAR' => 682,  // Saudi riyal
            'SGD' => 702,  // Singapore dollar
            'THB' => 764,  // Thai baht
            'TRY' => 949,  // Turkish lira
            'USD' => 840,  // United States dollar
            'ZAR' => 710,  // South African rand
            default => 356, // Fallback to INR
        };
    }

    /**
     * Validate payments.amount == sum(products.unitPrice * quantity).
     */
    public function validateAmount(Order $order, float $paymentsAmount): void
    {
        $order->loadMissing(['items']);
        $sum = $order->items->sum(fn ($i) => (float) $i->unit_price * (int) $i->quantity);
        $sum = round($sum, 2);
        if (abs($sum - $paymentsAmount) > 0.01) {
            throw new InvalidArgumentException("Payments amount {$paymentsAmount} does not match order total {$sum}.");
        }
    }
}
