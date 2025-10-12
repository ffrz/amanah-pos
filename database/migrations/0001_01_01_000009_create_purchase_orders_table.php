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
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->onDelete('restrict')->index(); // FK Index

            $table->string('type', 40)->nullable()->default('')->index();

            $table->string('supplier_name', 100)->nullable()->default('');
            $table->string('supplier_phone', 40)->nullable()->default('');
            $table->string('supplier_address', 200)->nullable()->default('');

            $table->string('status', 30)->index();
            $table->string('payment_status', 30)->index();
            $table->string('delivery_status', 30)->index();

            $table->datetime('datetime')->index();
            $table->date('due_date')->nullable()->index();

            $table->decimal('total', 18, 2)->default(0.);
            $table->decimal('total_paid', 18, 2)->default(0.);
            $table->decimal('total_discount', 18, 2)->default(0.);
            $table->decimal('total_tax', 18, 2)->default(0.);
            $table->decimal('grand_total', 18, 2)->default(0.);
            $table->decimal('remaining_debt', 18, 2)->default(0.)->index();

            $table->text('notes')->nullable();

            $table->createdDeletedTimestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
