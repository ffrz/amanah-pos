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
        Schema::create('tax_schemes', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique()->comment('Nama skema pajak (misal: PPN 11%)');
            $table->decimal('rate_percentage', 5, 2)->comment('Tarif pajak (misal: 11.00)');
            $table->boolean('is_inclusive')->default(false)->comment('TRUE jika harga sudah termasuk pajak (Inclusive)');
            $table->string('tax_authority', 50)->nullable()->comment('Otoritas penerima pajak');
            $table->text('description')->nullable();
            $table->boolean('active')->default(true);
            $table->createdUpdatedDeletedTimestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_schemes');
    }
};
