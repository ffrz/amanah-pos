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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('product_categories')->onDelete('restrict');
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->onDelete('restrict');
            $table->string('name', 100);
            $table->string('barcode', 255)->default('');
            $table->text('description')->nullable();
            $table->text('type', 20);
            $table->boolean('active')->default(true);
            $table->boolean('price_editable')->default(false);
            $table->decimal('cost',  10, 2)->default(0.);
            $table->decimal('price', 10, 2)->default(0.);
            $table->decimal('price_2', 10, 2)->default(0.);
            $table->decimal('price_3', 10, 2)->default(0.);
            $table->string('uom')->default('');
            $table->text('notes')->nullable();
            $table->decimal('stock', 10, 3)->default(0.);
            $table->decimal('min_stock', 10, 3)->default(0.);
            $table->decimal('max_stock', 10, 3)->default(0.);
            $table->createdUpdatedTimestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
