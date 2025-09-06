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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('product_categories')->onDelete('set null');
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->onDelete('set null');
            $table->string('name', 100);
            $table->string('barcode', 255)->default('');
            $table->text('description')->nullable();
            $table->text('type', 20);
            $table->boolean('active')->default(true);
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
