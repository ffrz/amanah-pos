<?php

use App\Models\SalesOrder;
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
            $table->foreignId('parent_id')->constrained('purchase_orders')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();

            $table->string('product_name', 100);
            $table->string('uom', 40)->default('');
            $table->decimal('quantity', 18, 3)->default(0.);
            $table->decimal('cost', 18, 2)->default(0.);
            $table->decimal('subtotal_cost', 18, 2)->default(0.);
            $table->string('notes', 100)->nullable();

            $table->index(['parent_id']);
            $table->index(['product_id']);
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
