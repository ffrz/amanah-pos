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
        Schema::create('operational_costs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('operational_cost_categories')->onDelete('set null');
            $table->foreignId('finance_account_id')->nullable()->constrained('finance_accounts')->onDelete('set null');
            $table->date('date');
            $table->string('description', 100)->default('');
            $table->decimal('amount', 8, 0)->default(0.);
            $table->text('notes');
            $table->createdUpdatedTimestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operational_costs');
    }
};
