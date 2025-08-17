<?php

use App\Models\Product;
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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('set null');
            $table->unsignedBigInteger('ref_id')->nullable();
            $table->string('ref_type', 20)->default('');
            $table->decimal('quantity', 10, 3)->default(0.);
            $table->createdUpdatedTimestamps();
            $table->index(['ref_id', 'ref_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
