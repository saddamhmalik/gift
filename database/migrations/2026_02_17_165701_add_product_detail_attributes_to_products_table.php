<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->json('denominations')->nullable()->after('max_price');
            $table->string('price_type', 32)->nullable()->after('currency_code');
            $table->string('product_type', 32)->nullable()->after('corporate_discounts');
            $table->string('purchaser_limit', 64)->nullable()->after('product_type');
            $table->text('purchaser_description')->nullable()->after('purchaser_limit');
            $table->string('tnc_link', 500)->nullable()->after('purchaser_description');
            $table->text('tnc_content')->nullable()->after('tnc_link');
            $table->json('woohoo_attributes')->nullable()->after('tnc_content');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'denominations',
                'price_type',
                'product_type',
                'purchaser_limit',
                'purchaser_description',
                'tnc_link',
                'tnc_content',
                'woohoo_attributes',
            ]);
        });
    }
};
