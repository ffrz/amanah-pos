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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('restrict');

            // Custom Polymorphic Index
            $table->unsignedBigInteger('ref_id')->nullable();
            $table->string('ref_type', 40)->default('');
            $table->index(['ref_type', 'ref_id']); // Combined index for fast polymorphic lookups

            $table->decimal('quantity', 10, 3)->default(0.);

            $table->string('product_name', 100)->default('');
            $table->string('uom', 40)->default('');
            $table->decimal('quantity_before', 10, 3)->default(0.);
            $table->decimal('quantity_after', 10, 3)->default(0.);
            $table->string('notes', 100);

            $table->createdUpdatedTimestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
