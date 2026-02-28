<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('phone_verified_at')->nullable()->after('phone');
            $table->string('pending_email')->nullable()->after('email');
            $table->string('email_change_token', 64)->nullable()->after('pending_email');
            $table->timestamp('email_change_expires_at')->nullable()->after('email_change_token');
            $table->string('pending_phone', 20)->nullable()->after('phone_verified_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone_verified_at',
                'pending_email',
                'email_change_token',
                'email_change_expires_at',
                'pending_phone',
            ]);
        });
    }
};
