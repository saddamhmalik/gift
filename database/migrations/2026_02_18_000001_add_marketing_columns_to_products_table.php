<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_featured')->default(false)->after('is_active');
            $table->boolean('is_trending')->default(false)->after('is_featured');
            $table->unsignedInteger('total_sales')->default(0)->after('is_trending');
            $table->unsignedInteger('popularity_score')->default(0)->after('total_sales');
            $table->unsignedInteger('views')->default(0)->after('popularity_score');
            $table->decimal('deal_price', 14, 2)->nullable()->after('views');
            $table->timestamp('deal_start')->nullable()->after('deal_price');
            $table->timestamp('deal_end')->nullable()->after('deal_start');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'is_featured', 'is_trending', 'total_sales', 'popularity_score',
                'views', 'deal_price', 'deal_start', 'deal_end',
            ]);
        });
    }
};
