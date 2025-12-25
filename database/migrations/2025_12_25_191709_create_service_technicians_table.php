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
        Schema::create('service_technicians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('name');
            // info tambahan ini bisa jadi penting ketika bisnis punya partner
            $table->string('phone', 100)->nullable()->default(null);
            $table->string('address', 200)->nullable()->default(null);
            $table->string('email', 100)->nullable()->default(null);

            $table->boolean('active')->default(false);
            $table->createdUpdatedDeletedTimestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_technicians');
    }
};
