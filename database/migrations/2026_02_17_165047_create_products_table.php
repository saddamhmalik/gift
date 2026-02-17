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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('external_id', 64)->index();
            $table->string('name');
            $table->string('slug');
            $table->string('url', 500)->nullable();
            $table->text('description')->nullable();
            $table->string('offer_short_desc', 500)->nullable();
            $table->string('currency_code', 10)->nullable();
            $table->decimal('min_price', 14, 2)->nullable();
            $table->decimal('max_price', 14, 2)->nullable();
            $table->string('image_url', 500)->nullable();
            $table->string('thumbnail_url', 500)->nullable();
            $table->json('related_product_options')->nullable();
            $table->json('corporate_discounts')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['category_id', 'external_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
