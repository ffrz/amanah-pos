<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Membuat tabel untuk unit-unit yang berbeda, barcode, dan harga default per unit.
     */
    public function up(): void
    {
        Schema::create('product_units', function (Blueprint $table) {
            $table->id();

            // Kunci asing ke tabel produk.
            $table->foreignId('product_id')
                ->constrained('products')
                ->onDelete('cascade')
                ->comment('ID Produk terkait.');

            // Nama Unit (e.g., 'PCS', 'Dus', 'Gross')
            $table->string('name', 50);

            // Barcode untuk unit ini. Setiap unit kemasan harus memiliki barcode unik.
            $table->string('barcode', 50)->nullable()->unique()->comment('Barcode unik untuk unit ini.');

            // Faktor konversi ke unit dasar (base unit).
            $table->decimal('conversion_factor', 10, 4)->default(1.0000)
                ->comment('Faktor konversi relatif terhadap unit dasar (base unit).');

            // Menandakan apakah unit ini adalah unit dasar (base unit).
            $table->boolean('is_base_unit')->default(false)->comment('True jika ini adalah unit dasar produk.');

            // --- Kolom Harga Default per Unit ---
            $table->decimal('cost', 10, 2)->default(0)->comment('Harga Pokok Penjualan (HPP) per unit.');
            $table->decimal('price_1', 10, 2)->default(0)->comment('Harga Jual 1 (Eceran) default.');
            $table->decimal('price_2', 10, 2)->default(0)->comment('Harga Jual 2 (Partai) default.');
            $table->decimal('price_3', 10, 2)->default(0)->comment('Harga Jual 3 (Grosir) default.');

            // Konfigurasi Harga Jual (Markup)
            $table->decimal('price_1_markup', 10, 2)->nullable();
            $table->decimal('price_2_markup', 10, 2)->nullable();
            $table->decimal('price_3_markup', 10, 2)->nullable();
            $table->string('price_1_option', 20)->nullable();
            $table->string('price_2_option', 20)->nullable();
            $table->string('price_3_option', 20)->nullable();

            // Batasan unik: (product_id, name)
            $table->unique(['product_id', 'name']);

            $table->createdUpdatedDeletedTimestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_units');
    }
};
