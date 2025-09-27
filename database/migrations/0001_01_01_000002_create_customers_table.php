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
            $table->string('type', 30); // santri, non santri (umum)
            $table->string('code', 40)->unique()->nullable(); // unique tapi nullable, pertimbangkan ganti ke nama lain agar lebih generik
            $table->string('name', 255);
            $table->string('email', 255)->nullable()->default(''); // untuk reset password
            $table->string('phone', 100)->nullable()->default('');
            $table->string('address', 200)->nullable()->default('');

            $table->decimal('wallet_balance', 15, 0)->default(0.); // wallet balance
            $table->decimal('balance', 15, 0)->default(0); // utang - piutang
            $table->boolean('active')->default(true);

            $table->string('password');
            $table->datetime('last_login_datetime')->nullable();
            $table->string('last_activity_description')->default('');
            $table->datetime('last_activity_datetime')->nullable();
            $table->rememberToken();

            $table->createdUpdatedTimestamps();
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
