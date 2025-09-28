<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migrasi untuk tabel User Activity Logs (Riwayat Aktivitas Pengguna).
 * Menggunakan struktur yang fleksibel dengan kolom 'category' dan 'meta' (JSON).
 */

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index(); // gak butuh relationship
            $table->string('username', 100)->index();
            $table->timestamp('logged_at')->useCurrent()->index();
            $table->string('activity_category', 50)->index();
            $table->string('activity_name', 50)->index();
            $table->text('description');
            $table->json('metadata')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 512)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_activity_logs');
    }
};
