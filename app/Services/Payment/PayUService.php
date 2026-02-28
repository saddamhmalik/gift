<?php

namespace App\Services\Payment;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Str;

class PayUService
{
    protected string $key;
    protected string $salt;
    protected string $payuUrl;
    protected string $frontendUrl;
    protected string $backendUrl;

    public function __construct()
    {
        $this->key         = config('payu.key');
        $this->salt        = config('payu.salt');
        $this->payuUrl     = config('payu.url');
        $this->frontendUrl = rtrim(config('payu.frontend_url'), '/');
        $this->backendUrl  = rtrim(config('payu.backend_url'), '/');
    }

    /**
     * Build all parameters needed to POST to PayU.
     * Generates a fresh unique txnid on every call so PayU never sees a duplicate,
     * even if the user retries payment on the same order.
     * The txnid is persisted on the order (payment_txnid column) for callback reconciliation.
     */
    public function buildPaymentParams(Order $order, User $user): array
    {
        $item        = $order->items->first();
        $productInfo = $item?->product?->name ?? 'Gift Card';
        // Amount charged via PayU = total minus any loyalty points applied
        $chargeAmount = max(1, (float) $order->total_amount - (float) $order->points_used);
        $amount       = number_format($chargeAmount, 2, '.', '');
        $firstName   = $user->first_name ?? explode(' ', $user->name ?? 'Customer')[0];
        $email       = $user->email;
        // PayU requires a phone for the form; ensure it's stored without + for the field
        // but we send whatever is on file (PayU is lenient, Woohoo is strict — Woohoo sanitizes separately)
        $phone       = $user->phone ? ltrim($user->phone, '+') : '9999999999';

        // Generate a unique txnid per payment attempt (max ~25 chars accepted by PayU)
        $txnId = $this->generateTxnId($order);

        // Persist so the callback handler can reconcile by order_id (udf1) OR txnid
        $order->update(['payment_txnid' => $txnId]);

        $surl = $this->backendUrl . '/api/v1/payment/payu/success';
        $furl = $this->backendUrl . '/api/v1/payment/payu/failure';

        $hash = $this->generateHash([
            'key'         => $this->key,
            'txnid'       => $txnId,
            'amount'      => $amount,
            'productinfo' => $productInfo,
            'firstname'   => $firstName,
            'email'       => $email,
            'udf1'        => (string) $order->id,
            'udf2'        => '',
            'udf3'        => '',
            'udf4'        => '',
            'udf5'        => '',
        ]);

        return [
            'payu_url'    => $this->payuUrl,
            'key'         => $this->key,
            'txnid'       => $txnId,
            'amount'      => $amount,
            'productinfo' => $productInfo,
            'firstname'   => $firstName,
            'lastname'    => $user->last_name ?? '',
            'email'       => $email,
            'phone'       => $phone,
            'surl'        => $surl,
            'furl'        => $furl,
            'hash'        => $hash,
            'udf1'        => (string) $order->id,
            'udf2'        => '',
            'udf3'        => '',
            'udf4'        => '',
            'udf5'        => '',
        ];
    }

    /**
     * Generate a unique txnid for each payment attempt.
     * Format: {order_id}_{random_8_chars}  e.g. "42_a3f9bc12"
     * - Always unique: random suffix is regenerated on every call
     * - Short enough for PayU's 25-char limit
     * - Embeds order_id for easy debugging
     */
    public function generateTxnId(Order $order): string
    {
        return $order->id . '_' . Str::random(12);
    }

    /**
     * Generate HMAC-SHA512 hash for PayU payment request.
     * Formula: sha512(key|txnid|amount|productinfo|firstname|email|udf1|udf2|udf3|udf4|udf5||||||SALT)
     */
    public function generateHash(array $params): string
    {
        $hashStr = implode('|', [
            $params['key'],
            $params['txnid'],
            $params['amount'],
            $params['productinfo'],
            $params['firstname'],
            $params['email'],
            $params['udf1'] ?? '',
            $params['udf2'] ?? '',
            $params['udf3'] ?? '',
            $params['udf4'] ?? '',
            $params['udf5'] ?? '',
            '', '', '', '', '', // udf6-udf10 (empty)
            $this->salt,
        ]);

        return hash('sha512', $hashStr);
    }

    /**
     * Verify PayU response hash (reverse hash formula).
     * Formula: sha512(SALT|status||||||udf5|udf4|udf3|udf2|udf1|email|firstname|productinfo|amount|txnid|key)
     */
    public function verifyResponseHash(array $params): bool
    {
        $hashStr = implode('|', [
            $this->salt,
            $params['status']       ?? '',
            '',                          // udf10
            '',                          // udf9
            '',                          // udf8
            '',                          // udf7
            '',                          // udf6
            $params['udf5']         ?? '',
            $params['udf4']         ?? '',
            $params['udf3']         ?? '',
            $params['udf2']         ?? '',
            $params['udf1']         ?? '',
            $params['email']        ?? '',
            $params['firstname']    ?? '',
            $params['productinfo']  ?? '',
            $params['amount']       ?? '',
            $params['txnid']        ?? '',
            $params['key']          ?? '',
        ]);

        $computed = hash('sha512', $hashStr);

        return hash_equals($computed, strtolower($params['hash'] ?? ''));
    }
}
