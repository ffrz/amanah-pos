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
        Schema::create('stock_adjustment_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('stock_adjustments')->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('set null');
            $table->string('product_name', 255)->default('');
            $table->decimal('old_quantity', 10, 3)->default(0.);
            $table->decimal('new_quantity', 10, 3)->default(0.);
            $table->decimal('balance', 10, 3)->default(0.);
            $table->string('uom', 100)->default('');
            $table->decimal('cost', 10, 2)->default(0.);
            $table->decimal('subtotal_cost', 10, 2)->default(0.);
            $table->decimal('price', 10, 2)->default(0.);
            $table->decimal('subtotal_price', 10, 2)->default(0.);
            $table->text('notes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_adjustment_details');
    }
};
