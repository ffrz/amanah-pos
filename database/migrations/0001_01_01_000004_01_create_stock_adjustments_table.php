<?php

use App\Models\Product;
use App\Models\StockAdjustment;
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
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->datetime('datetime')->nullable();
            $table->string('status', 30);
            $table->string('type', 30);
            $table->decimal('total_cost', 15, 2)->default(0.);
            $table->decimal('total_price', 15, 2)->default(0.);
            $table->text('notes')->nullable();
            $table->createdUpdatedTimestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_adjustments');
    }
};
