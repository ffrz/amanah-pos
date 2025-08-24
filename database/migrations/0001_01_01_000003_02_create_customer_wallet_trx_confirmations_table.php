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
        Schema::create('customer_wallet_trx_confirmations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('finance_account_id')->nullable()->constrained('finance_accounts')->onDelete('restrict');
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('restrict');
            $table->datetime('datetime')->nullable();
            $table->decimal('amount', 12, 2)->default(0.);
            $table->string('image_path', 255)->nullable();
            $table->string('status', 20);
            $table->text('notes')->nullable();
            $table->createdUpdatedTimestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_wallet_trx_confirmations');
    }
};
