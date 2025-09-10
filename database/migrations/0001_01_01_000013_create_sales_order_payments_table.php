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
        Schema::create('sales_order_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->nullable()->constrained('sales_orders')->onDelete('cascade');
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('set null');
            $table->foreignId('finance_account_id')->nullable()->constrained('finance_accounts')->onDelete('set null');
            $table->string('type', 20)->nullable();
            $table->decimal('amount', 18, 2)->nullable();
            $table->createdTimestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_order_payments');
    }
};
