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
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->cascadeOnDelete();

            $table->enum('status', array_keys(SalesOrder::Statuses))->default(SalesOrder::Status_Draft);
            $table->enum('payment_status', array_keys(SalesOrder::PaymentStatuses))->default(SalesOrder::PaymentStatus_Unpaid);
            $table->enum('delivery_status', array_keys(SalesOrder::DeliveryStatuses))->default(SalesOrder::DeliveryStatus_NotSent);
            $table->datetime('datetime');
            $table->date('due_date')->nullable();
            $table->decimal('total_cost', 18, 2);
            $table->decimal('total_price', 18, 2);
            $table->decimal('total_paid', 18, 2);
            $table->text('notes')->nullable();

            $table->datetime('created_datetime')->nullable();
            $table->datetime('updated_datetime')->nullable();
            $table->foreignId('created_by_uid')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by_uid')->nullable()->constrained('users')->nullOnDelete();

            $table->index(['customer_id']);
            $table->index(['status']);
            $table->index(['payment_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_orders');
    }
};
