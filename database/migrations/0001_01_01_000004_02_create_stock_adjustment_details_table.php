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
        Schema::create('stock_adjustment_details', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('parent_id');
            $table->unsignedBigInteger('product_id');
            $table->string('product_name', 255)->default('');

            $table->decimal('old_quantity', 10, 2)->default(0.);
            $table->decimal('new_quantity', 10, 2)->default(0.);
            $table->decimal('balance', 10, 2)->default(0.);

            $table->string('uom', 100)->default('');
            $table->decimal('cost', 10, 2)->default(0.);
            $table->decimal('subtotal_cost', 10, 2)->default(0.);
            $table->decimal('price', 10, 2)->default(0.);
            $table->decimal('subtotal_price', 10, 2)->default(0.);
            $table->text('notes')->nullable();

            $table->foreign('product_id')->references('id')->on('products')->onDelete('restrict');
            $table->foreign('parent_id')->references('id')->on('stock_adjustments')->onDelete('cascade');
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
