<?php

namespace App\Jobs;

use App\Models\LoyaltyPoint;
use App\Models\Order;
use App\Services\Payment\PayUService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Refund a failed order via PayU and reverse any loyalty transactions.
 * Dispatched automatically on hard Woohoo failures and on CANCELED status from polling.
 * Idempotent: skips if order is already refunded.
 */
class RefundOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 60;

    public function __construct(
        public Order $order,
        public string $reason = 'Order fulfillment failed',
    ) {
        $this->onQueue('default');
    }

    public function handle(PayUService $payUService): void
    {
        $order = Order::find($this->order->id);
        if (! $order) {
            return;
        }

        // Idempotency — skip if already refunded or refund is in flight
        if (in_array($order->refund_status, [Order::REFUND_STATUS_REFUNDED, Order::REFUND_STATUS_PENDING], true)) {
            Log::info('RefundOrderJob: skipping — refund already in progress or done', [
                'order_id'      => $order->id,
                'refund_status' => $order->refund_status,
            ]);

            return;
        }

        if (empty($order->payu_mihpayid)) {
            Log::warning('RefundOrderJob: no payu_mihpayid — cannot refund via PayU', [
                'order_id' => $order->id,
            ]);
            $order->update([
                'refund_status' => Order::REFUND_STATUS_FAILED,
                'refund_reason' => 'No payu_mihpayid stored — manual refund required',
            ]);

            return;
        }

        // Mark pending before external call so concurrent retries are blocked.
        // Also set delivery_status = failed so the UI shows the correct state immediately.
        $order->update([
            'refund_status'  => Order::REFUND_STATUS_PENDING,
            'refund_reason'  => $this->reason,
            'status'         => Order::STATUS_CANCELLED,
            'delivery_status' => \App\Services\Woohoo\WoohooOrderService::DELIVERY_STATUS_FAILED,
        ]);

        Log::info('RefundOrderJob: initiating PayU refund', [
            'order_id'   => $order->id,
            'mihpayid'   => $order->payu_mihpayid,
            'amount'     => $order->payu_paid_amount ?? $order->total_amount,
            'reason'     => $this->reason,
        ]);

        $result = $payUService->refundFull($order, $this->reason);

        if ($result['success']) {
            DB::transaction(function () use ($order) {
                $order->update([
                    'refund_status' => Order::REFUND_STATUS_REFUNDED,
                    'refunded_at'   => now(),
                ]);

                $this->reverseLoyalty($order);
            });

            Log::info('RefundOrderJob: refund successful', [
                'order_id' => $order->id,
                'message'  => $result['message'],
            ]);
        } else {
            $order->update([
                'refund_status' => Order::REFUND_STATUS_FAILED,
                'refund_reason' => $result['message'],
            ]);

            Log::error('RefundOrderJob: PayU refund failed', [
                'order_id'      => $order->id,
                'message'       => $result['message'],
                'payu_response' => $result['payu_response'],
            ]);

            // Re-throw so the job retries (up to $tries times)
            throw new \RuntimeException('PayU refund failed: ' . $result['message']);
        }
    }

    /**
     * Reverse loyalty transactions tied to this order on refund:
     * - Delete the credit row (earned points) — removes them from balance.
     * - Delete the debit row (redeemed points) — restores the deducted balance.
     * Also reset points_earned / points_used on the order.
     */
    protected function reverseLoyalty(Order $order): void
    {
        $user = $order->user;
        if (! $user) {
            return;
        }

        // Remove earned points (credit) — user didn't complete the purchase
        $creditDeleted = LoyaltyPoint::where('order_id', $order->id)
            ->where('type', LoyaltyPoint::TYPE_CREDIT)
            ->delete();

        // Restore redeemed points (debit) — user's points were not used
        $debitDeleted = LoyaltyPoint::where('order_id', $order->id)
            ->where('type', LoyaltyPoint::TYPE_DEBIT)
            ->delete();

        $order->update([
            'points_earned' => 0,
            'points_used'   => 0,
        ]);

        if ($creditDeleted > 0 || $debitDeleted > 0) {
            Log::info('RefundOrderJob: loyalty transactions reversed', [
                'order_id'       => $order->id,
                'credits_removed' => $creditDeleted,
                'debits_restored' => $debitDeleted,
            ]);
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('RefundOrderJob: all retries exhausted', [
            'order_id' => $this->order->id,
            'error'    => $exception->getMessage(),
        ]);

        // Mark failed so admin can manually review and retry
        Order::where('id', $this->order->id)
            ->where('refund_status', Order::REFUND_STATUS_PENDING)
            ->update(['refund_status' => Order::REFUND_STATUS_FAILED]);
    }
}
