<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Membuat tabel untuk harga bertingkat berdasarkan kuantitas (Tiered Pricing).
     */
    public function up(): void
    {
        Schema::create('product_quantity_prices', function (Blueprint $table) {
            $table->id();

            // Kunci asing ke product_units. Harga kuantitas berlaku per unit.
            $table->foreignId('product_unit_id')
                ->constrained('product_units')
                ->onDelete('cascade')
                ->comment('ID Unit Produk yang terkait.');

            // Tipe Harga (price_1, price_2, price_3) yang dimodifikasi oleh kuantitas.
            $table->enum('price_type', ['price_1', 'price_2', 'price_3'])
                ->comment('Tipe harga yang dimodifikasi.');

            // Kuantitas minimum untuk mendapatkan harga ini.
            $table->decimal('min_quantity', 10, 3)->default(1)->comment('Kuantitas minimum untuk rentang harga ini.');

            // Harga yang berlaku untuk rentang kuantitas ini.
            $table->decimal('price', 10, 2)->comment('Harga satuan yang berlaku.');

            // Batasan unik: Untuk satu unit dan satu tipe harga, min_quantity harus unik.
            $table->unique(['product_unit_id', 'price_type', 'min_quantity'], 'unit_price_qty_unique');

            // Menggunakan timestamps standar dan softDeletes. Sesuaikan jika Anda punya method custom.
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_quantity_prices');
    }
};
