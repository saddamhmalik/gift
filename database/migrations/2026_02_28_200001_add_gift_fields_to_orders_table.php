<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('order_mode',    ['SELF', 'GIFT'])->default('SELF')->after('currency_code');
            $table->enum('delivery_mode', ['API', 'EMAIL', 'SMS', 'ANY'])->default('API')->after('order_mode');
            $table->string('gift_recipient_name')->nullable()->after('delivery_mode');
            $table->string('gift_recipient_email')->nullable()->after('gift_recipient_name');
            $table->string('gift_recipient_phone')->nullable()->after('gift_recipient_email');
            $table->text('gift_message')->nullable()->after('gift_recipient_phone');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'order_mode', 'delivery_mode',
                'gift_recipient_name', 'gift_recipient_email',
                'gift_recipient_phone', 'gift_message',
            ]);
        });
    }
};
