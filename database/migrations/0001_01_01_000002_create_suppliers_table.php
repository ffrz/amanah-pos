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
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();

            $table->string('phone_1', 50)->default('')->index();
            $table->string('phone_2', 50)->default('')->index();
            $table->string('phone_3', 50)->default('')->index();

            $table->string('bank_account_name_1', 50)->default('');
            $table->string('bank_account_number_1', 50)->default('');
            $table->string('bank_account_holder_1', 100)->default('');

            $table->string('bank_account_name_2', 50)->default('');
            $table->string('bank_account_number_2', 50)->default('');
            $table->string('bank_account_holder_2', 100)->default('');

            $table->string('address', 255)->default('');
            $table->string('return_address', 255)->default('');

            $table->boolean('active')->default(true)->index();
            $table->decimal('balance', 15, 0)->default(0.)->index();

            $table->string('url_1', 255)->default('');
            $table->string('url_2', 255)->default('');

            $table->text('notes', 255)->nullable();

            $table->createdUpdatedTimestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
