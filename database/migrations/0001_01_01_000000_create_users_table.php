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
        // Create users table
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->enum('role', ['admin', 'user'])->default('user')->index();
            $table->text('refresh_token')->nullable();
            $table->index('refresh_token');
            $table->timestamp('refresh_token_expires_at')->nullable();
            $table->string('google_id')->nullable();
            $table->rememberToken();
            $table->timestamps();

            // Two-Factor Authentication (2FA)
            $table->boolean('two_factor_enabled')->default(false);
            $table->text('two_factor_secret')->nullable();
            $table->string('two_factor_otp')->nullable();
            $table->timestamp('two_factor_expires_at')->nullable();
        });

        // Create password reset tokens table
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->id(); // Changed from email as primary key
            $table->string('email')->index();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};