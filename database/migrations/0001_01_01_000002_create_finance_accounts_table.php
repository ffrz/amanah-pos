<?php

use App\Models\FinanceAccount;
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
        Schema::create('finance_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name', 40)->unique();
            $table->string('cashier_code', 40)->nullable();
            $table->string('type', 30);
            $table->string('bank', 40)->default('');
            $table->string('number', 20)->default('');
            $table->string('holder', 100)->default('');
            $table->decimal('balance', 15, 0)->default(0.);
            $table->boolean('active')->default(true);
            $table->boolean('show_in_pos_payment')->default(false);
            $table->boolean('show_in_purchasing_payment')->default(false);
            $table->boolean('has_wallet_access')->default(false);
            $table->text('notes')->nullable();
            $table->createdUpdatedTimestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('cashier_account_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('finance_accounts');
    }
};
