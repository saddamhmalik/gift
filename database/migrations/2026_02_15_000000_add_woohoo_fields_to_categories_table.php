<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('url', 500)->nullable()->after('slug');
            $table->text('short_description')->nullable()->after('description');
            $table->string('canonical_url', 500)->nullable()->after('short_description');
            $table->string('bg_color_code', 20)->nullable()->after('color_code');
            $table->string('thumbnail_url', 500)->nullable()->after('image_url');
            $table->boolean('sub_category_filter')->default(false)->after('offer_description');
            $table->unsignedInteger('subcategories_count')->default(0)->after('sub_category_filter');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn([
                'url',
                'short_description',
                'canonical_url',
                'bg_color_code',
                'thumbnail_url',
                'sub_category_filter',
                'subcategories_count',
            ]);
        });
    }
};
