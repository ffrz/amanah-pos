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
        Schema::create('customer_wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('finance_account_id')->nullable()->constrained('finance_accounts')->onDelete('set null');
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('set null');
            $table->nullableMorphs('ref');
            $table->datetime('datetime')->nullable(); // transaction date time
            $table->string('type', 30);
            $table->decimal('amount', 12, 2)->default(0.);
            $table->text('notes')->nullable();
            $table->createdUpdatedTimestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_wallet_transactions');
    }
};
