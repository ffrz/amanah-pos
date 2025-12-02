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
        Schema::table('purchase_order_details', function (Blueprint $table) {
            // Diskon dalam Persen (misal: 10.5%)
            $table->decimal('discount_percent', 5, 2)->default(0)->after('subtotal_cost');

            // Diskon dalam Rupiah PER ITEM (misal: Rp 5000)
            $table->decimal('discount_amount', 15, 2)->default(0)->after('discount_percent');

            // [BARU] Total Diskon Baris Ini (discount_amount * quantity)
            // Cache ini mempercepat rekap laporan "Total Hemat" tanpa hitung ulang
            $table->decimal('subtotal_discount', 15, 2)->default(0)->after('discount_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_order_details', function (Blueprint $table) {
            $table->dropColumn(['discount_percent', 'discount_amount', 'subtotal_discount']);
        });
    }
};
