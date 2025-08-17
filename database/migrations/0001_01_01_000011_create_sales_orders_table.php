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
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('set null');
            $table->string('status', 30);
            $table->string('payment_status', 30);
            $table->string('delivery_status', 30);
            $table->datetime('datetime');
            $table->date('due_date')->nullable();
            $table->decimal('total_cost', 18, 2)->default(0.);
            $table->decimal('total_price', 18, 2)->default(0.);
            $table->decimal('total_paid', 18, 2)->default(0.);
            $table->text('notes')->nullable();
            $table->createdUpdatedTimestamps();
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
