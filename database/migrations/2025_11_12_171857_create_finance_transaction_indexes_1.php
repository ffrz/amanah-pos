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

        // Ganti 'sales_orders' dengan nama tabel yang sebenarnya jika berbeda
        Schema::table('finance_transactions', function (Blueprint $table) {
            $table->index(['created_at', 'account_id']);
            $table->index(['datetime', 'type', 'account_id', 'deleted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('finance_transactions', function (Blueprint $table) {
            $table->dropIndex(['created_at', 'account_id']);
            $table->dropIndex(['datetime', 'type', 'account_id', 'deleted_at']);
        });
    }
};
