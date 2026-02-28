<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loyalty_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('type', ['credit', 'debit']);
            $table->decimal('points', 10, 2);
            $table->string('description');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'type', 'expires_at']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('points_used', 10, 2)->default(0)->after('total_amount');
            $table->decimal('points_earned', 10, 2)->default(0)->after('points_used');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalty_points');
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['points_used', 'points_earned']);
        });
    }
};
