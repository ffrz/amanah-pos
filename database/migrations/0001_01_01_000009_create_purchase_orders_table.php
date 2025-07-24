<?php

use App\Models\PurchaseOrder;
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
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->cascadeOnDelete();

            $table->enum('status', array_keys(PurchaseOrder::Statuses))->default(PurchaseOrder::Status_Draft);
            $table->enum('payment_status', array_keys(PurchaseOrder::PaymentStatuses))->default(PurchaseOrder::PaymentStatus_Unpaid);
            $table->enum('delivery_status', array_keys(PurchaseOrder::DeliveryStatuses))->default(PurchaseOrder::DeliveryStatus_NotSent);

            $table->datetime('datetime');
            $table->date('due_date')->nullable();
            $table->decimal('total', 18, 2)->default(0.);
            $table->decimal('total_paid', 18, 2)->default(0.);
            $table->text('notes')->nullable();

            $table->datetime('created_datetime')->nullable();
            $table->datetime('updated_datetime')->nullable();
            $table->foreignId('created_by_uid')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by_uid')->nullable()->constrained('users')->nullOnDelete();

            $table->index(['supplier_id']);
            $table->index(['status']);
            $table->index(['payment_status']);
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
