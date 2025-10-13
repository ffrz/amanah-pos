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
        Schema::create('sales_order_return_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_order_return_id')
                ->constrained('sales_order_returns')
                ->onDelete('cascade');

            // // Merujuk ke detail penjualan asli (Opsional, untuk audit)
            // $table->foreignId('sales_order_detail_id')
            //     ->nullable()
            //     ->constrained('sales_order_details')
            //     ->onDelete('set null');
            $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('restrict');

            $table->string('product_name', 100)->default('');
            $table->string('product_barcode', 100)->default('')->index();
            $table->string('product_description', 100)->default('');
            $table->string('product_uom', 40)->default('');
            $table->decimal('quantity', 18, 3)->default(0.);
            $table->decimal('cost', 18, 2)->default(0.);
            $table->decimal('price', 18, 2)->default(0.);
            $table->decimal('subtotal_cost', 18, 2)->default(0.);
            $table->decimal('subtotal_price', 18, 2)->default(0.);
            $table->string('notes', 100)->nullable();

            $table->createdUpdatedDeletedTimestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_order_return_details');
    }
};
