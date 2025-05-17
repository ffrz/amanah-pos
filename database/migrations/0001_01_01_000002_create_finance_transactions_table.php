<?php

use App\Models\FinanceTransaction;
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
        Schema::create('finance_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id')->nullable();
            $table->nullableMorphs('ref');
            $table->datetime('datetime')->nullable();
            $table->enum('type', array_keys(FinanceTransaction::Types));
            $table->decimal('amount', 12, 2);
            $table->text('notes')->nullable();

            $table->datetime('created_datetime')->nullable();
            $table->datetime('updated_datetime')->nullable();
            $table->unsignedBigInteger('created_by_uid')->nullable();
            $table->unsignedBigInteger('updated_by_uid')->nullable();

            $table->foreign('account_id')->references('id')->on('finance_accounts')->onDelete('cascade');
            $table->foreign('created_by_uid')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by_uid')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('finance_transactions');
    }
};
