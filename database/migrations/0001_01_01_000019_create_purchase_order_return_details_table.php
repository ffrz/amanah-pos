<?php

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
        Schema::create('purchase_order_return_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_return_id')
                ->constrained('purchase_order_returns')
                ->onDelete('cascade');

            // // Merujuk ke detail penjualan asli (Opsional, untuk audit)
            // $table->foreignId('purchase_order_detail_id')
            //     ->nullable()
            //     ->constrained('purchase_order_details')
            //     ->onDelete('set null');
            $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('restrict');

            $table->string('product_name', 100)->default('');
            $table->string('product_barcode', 100)->default('')->index();
            $table->string('product_description', 100)->default('');
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
        Schema::dropIfExists('purchase_order_return_details');
    }
};
