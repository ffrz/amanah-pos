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
        Schema::create('finance_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->unique();
            $table->string('type', 30)->index();
            $table->string('bank', 40)->default('');
            $table->string('number', 20)->default('');
            $table->string('holder', 100)->default('');
            $table->decimal('balance', 15, 0)->default(0.)->index();
            $table->boolean('active')->default(true)->index();
            $table->boolean('show_in_pos_payment')->default(false)->index();
            $table->boolean('show_in_purchasing_payment')->default(false)->index();
            $table->boolean('has_wallet_access')->default(false)->index();
            $table->text('notes')->nullable();
            $table->createdUpdatedDeletedTimestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('finance_accounts');
    }
};
