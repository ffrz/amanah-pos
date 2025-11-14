<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_transactions_has_tags', function (Blueprint $table) {
            $table->unsignedBigInteger('finance_transaction_id');
            $table->unsignedBigInteger('finance_transaction_tag_id');

            // Index untuk query cepat
            $table->index('finance_transaction_id');
            $table->index('finance_transaction_tag_id');

            // Foreign Key (aman dan auto cascade)
            $table->foreign('finance_transaction_id')
                ->references('id')->on('finance_transactions')
                ->onDelete('cascade');

            $table->foreign('finance_transaction_tag_id')
                ->references('id')->on('finance_transaction_tags')
                ->onDelete('cascade');

            // Mencegah duplikasi tag pada transaksi yang sama
            $table->unique(
                ['finance_transaction_id', 'finance_transaction_tag_id'],
                'ft_tag_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_transactions_has_tags');
    }
};
