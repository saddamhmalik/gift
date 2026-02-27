<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Stores the unique txnid sent to PayU for each payment attempt.
            // A new value is generated every time the user initiates payment,
            // so retries never reuse the same txnid and PayU won't reject them.
            $table->string('payment_txnid')->nullable()->after('order_token')->index();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('payment_txnid');
        });
    }
};
