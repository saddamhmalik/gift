<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\Woohoo\WoohooActivatedCardsService;
use App\Services\Woohoo\WoohooOrderService;
use Illuminate\Console\Command;

class FetchWoohooActivatedCards extends Command
{
    protected $signature = 'woohoo:fetch-cards
                            {order : Local order ID or Woohoo order ID}
                            {--force : Re-fetch even if cards are already stored}';

    protected $description = 'Fetch activated card details from Woohoo Activated Cards API and store them on the order.';

    public function handle(
        WoohooActivatedCardsService $activatedCards,
        WoohooOrderService $orderService
    ): int {
        $identifier = $this->argument('order');

        $order = Order::where('id', $identifier)
            ->orWhere('woohoo_order_id', $identifier)
            ->orWhere('woohoo_refno', $identifier)
            ->first();

        if (! $order) {
            $this->error("Order not found: {$identifier}");
            return self::FAILURE;
        }

        if (! $order->woohoo_order_id) {
            $this->error("Order #{$order->id} has no Woohoo order ID yet.");
            return self::FAILURE;
        }

        if ($order->card_details_encrypted && ! $this->option('force')) {
            $this->warn("Order #{$order->id} already has card details. Use --force to re-fetch.");
            return self::SUCCESS;
        }

        $this->info("Fetching activated cards for Woohoo order {$order->woohoo_order_id}...");

        $result = $activatedCards->fetchAndNormalize($order->woohoo_order_id);

        if (! $result['success']) {
            $httpStatus = $result['http_status'];
            $state      = $result['state'] ?? 'UNKNOWN';
            $message    = $result['message'] ?? '-';

            if ($httpStatus === WoohooActivatedCardsService::STATUS_PROCESSING) {
                $this->warn("Cards are still being activated (PROCESSING). Try again in a moment.");
            } elseif ($httpStatus === WoohooActivatedCardsService::STATUS_CANCELLED) {
                $this->error("Order is CANCELLED on Woohoo (state={$state}).");
            } else {
                $this->error("Activated Cards API failed [HTTP {$httpStatus}]: {$message}");
            }
            return self::FAILURE;
        }

        $count = count($result['cards']);
        if ($count === 0) {
            $this->warn("API returned 0 cards (total_cards={$result['total_cards']}).");
            return self::SUCCESS;
        }

        $orderService->storeCardDetailsEncrypted($order, $result);

        // Also mark the order as completed/fulfilled if it isn't already
        if ($order->status !== Order::STATUS_COMPLETED) {
            $order->update([
                'status'          => Order::STATUS_COMPLETED,
                'delivery_status' => WoohooOrderService::DELIVERY_STATUS_FULFILLED,
            ]);
            $this->line("  → Order status updated to completed/fulfilled.");
        }

        $this->info("✓ Stored {$count} activated card(s) for order #{$order->id}.");

        // Print a summary
        $this->table(['#', 'SKU', 'Card Number', 'PIN / Code', 'Amount', 'Validity'], array_map(
            fn ($card, $i) => [
                $i + 1,
                $card['sku'] ?? '-',
                $card['cardNumber'] ?? '-',
                $card['cardPin'] ?? $card['activationCode'] ?? '-',
                isset($card['amount']) ? '₹' . $card['amount'] : '-',
                isset($card['validity']) ? date('d M Y', strtotime($card['validity'])) : '-',
            ],
            $result['cards'],
            array_keys($result['cards'])
        ));

        return self::SUCCESS;
    }
}
