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
            // $table->foreignId('tax_scheme_id')->nullable()->constrained('tax_schemes')->onDelete('restrict');

            $table->string('name', 255)->unique();
            $table->string('barcode', 255)->default('')->index();
            $table->text('description')->nullable();
            $table->string('type', 20)->index();
            $table->boolean('active')->default(true)->index();
            $table->boolean('price_editable')->default(false);
            // $table->boolean('tax_enabled')->default(false);
            $table->decimal('cost', 10, 2)->default(0.);
            $table->decimal('price_1', 10, 2)->default(0.);
            $table->decimal('price_2', 10, 2)->default(0.);
            $table->decimal('price_3', 10, 2)->default(0.);

            $table->decimal('price_1_markup', 5, 2)->default(0.);
            $table->decimal('price_2_markup', 5, 2)->default(0.);
            $table->decimal('price_3_markup', 5, 2)->default(0.);

            $table->string('price_1_option')->default('price');
            $table->string('price_2_option')->default('price');
            $table->string('price_3_option')->default('price');

            $table->date('expiry_date', 'date')->nullable();
            $table->string('uom', 20)->nullable()->default('');
            $table->text('notes')->nullable();
            $table->decimal('stock', 10, 3)->default(0.)->index();
            $table->decimal('min_stock', 10, 3)->default(0.);
            $table->decimal('max_stock', 10, 3)->default(0.);

            $table->createdUpdatedDeletedTimestamps();
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
