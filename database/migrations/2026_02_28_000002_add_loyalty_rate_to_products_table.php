<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Null = use global default rate from config('loyalty.default_rate')
            $table->decimal('loyalty_rate', 5, 4)->nullable()->after('is_active')
                ->comment('Loyalty earn rate, e.g. 0.01 = 1%. Null = use global default.');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('loyalty_rate');
        });
    }
};
