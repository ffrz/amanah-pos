<?php

/**
 * Proprietary Software / Perangkat Lunak Proprietary
 * Copyright (c) 2025 Fahmi Fauzi Rahman. All rights reserved.
 * 
 * EN: Unauthorized use, copying, modification, or distribution is prohibited.
 * ID: Penggunaan, penyalinan, modifikasi, atau distribusi tanpa izin dilarang.
 * 
 * See the LICENSE file in the project root for full license information.
 * Lihat file LICENSE di root proyek untuk informasi lisensi lengkap.
 * 
 * GitHub: https://github.com/ffrz
 * Email: fahmifauzirahman@gmail.com
 */

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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('type', 30)->index();
            $table->string('code', 255)->unique();
            $table->string('name', 255)->index();
            $table->string('email', 255)->nullable()->default('')->index(); // untuk reset password
            $table->string('phone', 100)->nullable()->default('')->index();
            $table->string('address', 200)->nullable()->default('');

            $table->decimal('wallet_balance', 15, 0)->default(0.)->index(); // deposit
            $table->decimal('balance', 15, 0)->default(0)->index();
            $table->boolean('active')->default(true)->index();

            $table->decimal('credit_limit', 15, 0)->default(0);
            $table->boolean('credit_allowed')->default(true);

            $table->string('default_price_type', 10)->nullable('price_1');

            $table->string('password');
            $table->datetime('last_login_datetime')->nullable()->index();
            $table->string('last_activity_description')->default('');
            $table->datetime('last_activity_datetime')->nullable();
            $table->rememberToken();

            $table->createdUpdatedDeletedTimestamps();
        });

        Schema::create('customer_password_reset_tokens', function (Blueprint $table) {
            $table->string('code')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('customer_sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('customer_account_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_sessions');
        Schema::dropIfExists('customer_password_reset_tokens');
        Schema::dropIfExists('customers');
    }
};
