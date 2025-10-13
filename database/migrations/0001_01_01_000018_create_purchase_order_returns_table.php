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
        Schema::create('purchase_order_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->onDelete('restrict');
            $table->foreignId('cashier_id')->nullable()->constrained('users')->onDelete('restrict');
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->onDelete('restrict');
            $table->string('supplier_code', 100)->nullable()->default('');
            $table->string('supplier_name', 100)->nullable()->default('');
            $table->string('supplier_phone', 40)->nullable()->default('');
            $table->string('supplier_address', 200)->nullable()->default('');
            $table->string('status', 30)->index();
            $table->string('refund_status', 30)->index();
            $table->datetime('datetime')->index();

            $table->decimal('total_cost', 18, 2)->default(0.);
            $table->decimal('total_discount', 18, 2)->default(0.);
            $table->decimal('total_tax', 18, 2)->default(0.);
            $table->decimal('grand_total', 18, 2)->default(0.);

            $table->decimal('total_refunded', 18, 2)->default(0.);
            $table->decimal('remaining_refund', 18, 2)->default(0.)->index();

            $table->string('reason', 255)->nullable();
            $table->text('notes')->nullable();

            $table->createdUpdatedDeletedTimestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_order_returns');
    }
};
