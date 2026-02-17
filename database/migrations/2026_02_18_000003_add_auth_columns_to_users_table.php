<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 20)->nullable()->unique()->after('email');
            $table->string('google_id')->nullable()->unique()->after('phone');
            $table->string('avatar')->nullable()->after('google_id');
            $table->string('otp', 6)->nullable()->after('avatar');
            $table->timestamp('otp_expires_at')->nullable()->after('otp');
        });

        // Email/password nullable for OTP/Google-only users (requires doctrine/dbal for change())
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'google_id', 'avatar', 'otp', 'otp_expires_at']);
        });
    }
};
