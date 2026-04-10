<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'payu_mihpayid')) {
                $table->string('payu_mihpayid')->nullable()->after('payment_txnid')->index();
            }
            if (! Schema::hasColumn('orders', 'payu_paid_amount')) {
                $table->decimal('payu_paid_amount', 10, 2)->nullable()->after('payu_mihpayid');
            }
            if (! Schema::hasColumn('orders', 'refund_status')) {
                $table->string('refund_status', 20)->nullable()->after('payu_paid_amount')->index();
            }
            if (! Schema::hasColumn('orders', 'refund_reason')) {
                $table->string('refund_reason')->nullable()->after('refund_status');
            }
            if (! Schema::hasColumn('orders', 'refunded_at')) {
                $table->timestamp('refunded_at')->nullable()->after('refund_reason');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['payu_mihpayid', 'payu_paid_amount', 'refund_status', 'refund_reason', 'refunded_at']);
        });
    }
};
