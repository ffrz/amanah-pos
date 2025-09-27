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
        Schema::create('operational_costs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('operational_cost_categories')->onDelete('restrict'); // Added index on foreign key
            $table->foreignId('finance_account_id')->nullable()->constrained('finance_accounts')->onDelete('restrict'); // Added index on foreign key
            $table->date('date')->index(); // Crucial index for filtering by date/report period
            $table->string('description', 100)->default('')->index(); // Added index for searching/sorting description
            $table->string('image_path', 255)->nullable()->default('');
            $table->decimal('amount', 8, 0)->default(0.);
            $table->text('notes');
            $table->createdUpdatedTimestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operational_costs');
    }
};
