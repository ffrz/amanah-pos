<?php

/**
 * Proprietary Software / Perangkat Lunak Proprietary
 * Copyright (c) 2025 Fahmi Fauzi Rahman. All rights reserved.
 * * EN: Unauthorized use, copying, modification, or distribution is prohibited.
 * ID: Penggunaan, penyalinan, modifikasi, atau distribusi tanpa izin dilarang.
 * * See the LICENSE file in the project root for full license information.
 * Lihat file LICENSE di root proyek untuk informasi lisensi lengkap.
 * * GitHub: https://github.com/ffrz
 * Email: fahmifauzirahman@gmail.com
 */

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
        Schema::create('cashier_cash_drops', function (Blueprint $table) {
            $table->id();

            // Identitas Transaksi
            $table->string('code')->unique(); // CD-YYYYMMDD-XXXX
            $table->dateTime('datetime');

            // Pelaku & Lokasi
            $table->foreignId('cashier_id')->constrained('users'); // Siapa yang setor
            $table->foreignId('terminal_id')->nullable()->constrained('cashier_terminals'); // Dari mesin mana
            $table->foreignId('cashier_session_id')->nullable()->constrained('cashier_sessions'); // Sesi kasir yang mana

            // Alur Uang
            // Biasanya source otomatis diambil dari akun kasir/drawer, target adalah Brankas Utama/Bank
            $table->foreignId('source_finance_account_id')->constrained('finance_accounts');
            $table->foreignId('target_finance_account_id')->constrained('finance_accounts');

            // Perhitungan
            $table->decimal('amount', 15, 2); // Uang Fisik (Real)

            // Status & Approval
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->foreignId('approved_by')->nullable()->constrained('users'); // Supervisor
            $table->dateTime('approved_at')->nullable();

            // Lain-lain
            $table->text('notes')->nullable(); // Alasan selisih, dll
            $table->string('image_path')->nullable(); // Bukti foto

            $table->createdUpdatedDeletedTimestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cashier_cash_drops');
    }
};
