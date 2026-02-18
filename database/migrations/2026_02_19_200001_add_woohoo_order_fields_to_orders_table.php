<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('woohoo_refno', 64)->nullable()->unique()->after('currency_code');
            $table->string('woohoo_order_id', 64)->nullable()->index()->after('woohoo_refno');
            $table->boolean('woohoo_sync')->default(false)->after('woohoo_order_id');
            $table->text('card_details_encrypted')->nullable()->after('woohoo_sync');
            $table->string('delivery_status', 32)->nullable()->after('card_details_encrypted');
            $table->string('billing_email')->nullable()->after('delivery_status');
            $table->string('billing_telephone', 32)->nullable()->after('billing_email');
            $table->string('billing_name')->nullable()->after('billing_telephone');
            $table->json('address')->nullable()->after('billing_name');
            $table->json('woohoo_request')->nullable()->after('address');
            $table->json('woohoo_response')->nullable()->after('woohoo_request');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'woohoo_refno', 'woohoo_order_id', 'woohoo_sync', 'card_details_encrypted',
                'delivery_status', 'billing_email', 'billing_telephone', 'billing_name',
                'address', 'woohoo_request', 'woohoo_response',
            ]);
        });
    }
};
