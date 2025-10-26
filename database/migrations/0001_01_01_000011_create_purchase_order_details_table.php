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
        Schema::create('purchase_order_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->nullable()->constrained('purchase_orders')->onDelete('cascade');
            $table->foreignId('return_id')->nullable()->constrained('purchase_order_returns')->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('restrict');

            $table->string('product_name', 100);
            $table->string('product_uom', 40)->default('');
            $table->decimal('quantity', 18, 3)->default(0.);

            $table->decimal('user_input_qty', 10, 3)->nullable();  // Kuantitas yang dimasukkan Kasir/Admin
            $table->string('user_input_uom', 20)->nullable();      // Satuan yang dipilih (e.g., 'Box')
            $table->decimal('conversion_rate', 10, 3)->nullable(); // Nilai konversi saat transaksi

            $table->decimal('cost', 18, 2)->default(0.);
            $table->decimal('subtotal_cost', 18, 2)->default(0.);
            $table->string('notes', 100)->nullable();

            $table->createdUpdatedDeletedTimestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_order_details');
    }
};
