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
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('phone', 100)->default('');
            $table->string('bank_account_number', 40)->default('');
            $table->string('address', 200)->default('');
            $table->string('return_address', 200)->default('');
            $table->boolean('active')->default(true);
            $table->createdUpdatedTimestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
