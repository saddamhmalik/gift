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
        Schema::table('categories', function (Blueprint $table) {
            $table->boolean('meta_index')->nullable()->after('subcategories_count');
            $table->string('meta_keyword', 500)->nullable()->after('meta_index');
            $table->string('page_title', 255)->nullable()->after('meta_keyword');
            $table->text('meta_description')->nullable()->after('page_title');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['meta_index', 'meta_keyword', 'page_title', 'meta_description']);
        });
    }
};
