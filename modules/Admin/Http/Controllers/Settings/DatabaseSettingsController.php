<?php

/**
 * Proprietary Software / Perangkat Lunak Proprietary
 * Copyright (c) 2025 Fahmi Fauzi Rahman. All rights reserved.
 *
 * EN: Unauthorized use, copying, modification, or distribution is prohibited.
 * ID: Penggunaan, penyalinan, modifikasi, atau distribusi tanpa izin dilarang.
 *
 * See the LICENSE file in the project root for full license information.
 * Lihat file LICENSE di root proyek untuk informasi lisensi lengkap.
 *
 * GitHub: https://github.com/ffrz
 * Email: fahmifauzirahman@gmail.com
 */

namespace Modules\Admin\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Modules\Admin\Services\UserActivityLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use ZipArchive;

class DatabaseSettingsController extends Controller
{
    /**
     * @var UserActivityLogService
     */
    protected $userActivityLogService;

    public function __construct(UserActivityLogService $userActivityLogService)
    {
        $this->userActivityLogService = $userActivityLogService;
    }
    /**
     * Tampilkan halaman indeks pengguna.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        return inertia('settings/database/Index');
    }

    public function backup(Request $request)
    {
        // Opsi dari UI (Default: Full Backup)
        // mode: 'full', 'structure_only', 'data_only'
        // safe_mode: true (pakai INSERT IGNORE biar pas restore gak error duplicate), false (INSERT biasa)
        $mode = $request->input('mode', 'full');
        $safeMode = $request->boolean('safe_mode', false);

        $filename = 'backup_' . $mode . '_' . date('Y-m-d_H-i-s') . '.sql';
        $path = storage_path('app/backups/' . $filename);

        // Pastikan folder ada
        if (!file_exists(dirname($path))) mkdir(dirname($path), 0755, true);

        try {
            // Konfigurasi Database
            $db = config('database.connections.mysql');
            $dsn = "mysql:host={$db['host']};dbname={$db['database']};port={$db['port']}";

            // --- SETTINGAN SAKTI (Memenuhi request Anda) ---
            $dumpSettings = [
                'compress' => IMysqldump\Mysqldump::NONE,
                'default-character-set' => IMysqldump\Mysqldump::UTF8,

                // Fitur Chunking (Parsial) bawaan library agar hemat RAM
                'net_buffer_length' => 1000000,

                // Logika Structure vs Data
                'no-data' => ($mode === 'structure_only'), // Jika true, cuma create table
                'no-create-info' => ($mode === 'data_only'), // Jika true, cuma insert data

                // Logika "Skip Insert jika ada" (Restore aman)
                // Jika safe_mode aktif, ganti INSERT jadi INSERT IGNORE
                'insert-ignore' => $safeMode,

                // Pastikan trigger/procedure ikut (opsional)
                'add-drop-table' => true,
                'databases' => false, // Jangan sertakan 'CREATE DATABASE'
            ];

            // Eksekusi (Murni PHP, tanpa mysqldump.exe)
            $dump = new IMysqldump\Mysqldump($dsn, $db['username'], $db['password'], $dumpSettings);
            $dump->start($path);

            return response()->download($path)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return back()->with('error', 'Backup Gagal: ' . $e->getMessage());
        }
    }

    /**
     * RESTORE (Pure PHP Fallback)
     * Karena file SQL sudah kita atur saat backup (Insert Ignore dsb),
     * restore-nya cukup eksekusi raw query saja.
     */
    public function restore(Request $request)
    {
        $request->validate(['file' => 'required|file|max:102400']); // 100MB Limit

        $file = $request->file('file');
        // Jika file zip, extract dulu (gunakan kode zip sebelumnya jika perlu)
        // Untuk contoh ini kita asumsi .sql langsung biar ringkas

        // Membaca file stream (Hemat Memori untuk file besar)
        // Kita tidak pakai file_get_contents agar server tidak crash
        $stream = fopen($file->getRealPath(), 'r');

        if (!$stream) return back()->with('error', 'Gagal membuka file.');

        DB::disableQueryLog();
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        try {
            // Teknik Chunking Restore Manual
            // Membaca per baris dan mengumpulkan query sampai titik koma (;)
            $queryBuffer = '';

            while (($line = fgets($stream)) !== false) {
                // Skip komentar
                if (str_starts_with($line, '--') || str_starts_with($line, '/*') || trim($line) === '') {
                    continue;
                }

                $queryBuffer .= $line;

                // Jika baris diakhiri titik koma, eksekusi query
                if (trim($line) !== '' && str_ends_with(trim($line), ';')) {
                    DB::unprepared($queryBuffer);
                    $queryBuffer = ''; // Reset buffer
                }
            }

            fclose($stream);
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            return back()->with('success', 'Restore Selesai.');
        } catch (\Exception $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            return back()->with('error', 'Restore Error: ' . $e->getMessage());
        }
    }

    /**
     * RESET TRANSAKSI SAJA
     * Menghapus data penjualan, stok, log, dll.
     * Tapi membiarkan User, Produk, Pelanggan tetap ada.
     */
    public function resetTransaction()
    {
        // 1. DAFTAR TABEL YANG AKAN DIKOSONGKAN (WAJIB DISESUAIKAN)
        // Masukkan nama-nama tabel transaksi Anda di sini.
        $tablesToTruncate = [
            'sales',
            'sale_items',
            'purchases',
            'purchase_items',
            'inventory_logs',       // Riwayat keluar masuk barang
            'cash_flows',           // Arus kas
            'expenses',             // Pengeluaran
            'activity_log',         // Log aktivitas user (Spatie)
            'notifications',        // Notifikasi sistem
            // 'failed_jobs',       // Opsional
            // 'jobs',              // Opsional
        ];

        try {
            // Matikan pengecekan Foreign Key agar tidak error urutan penghapusan
            Schema::disableForeignKeyConstraints();

            foreach ($tablesToTruncate as $table) {
                if (Schema::hasTable($table)) {
                    DB::table($table)->truncate();
                }
            }

            // Nyalakan kembali
            Schema::enableForeignKeyConstraints();

            // Bersihkan cache jika perlu
            Artisan::call('cache:clear');

            return back()->with('success', 'Data transaksional berhasil di-reset. Data master aman.');
        } catch (\Exception $e) {
            Schema::enableForeignKeyConstraints();
            return back()->with('error', 'Gagal mereset transaksi: ' . $e->getMessage());
        }
    }

    /**
     * RESET TOTAL (FACTORY RESET)
     * Mengembalikan aplikasi seperti baru diinstall.
     * Semua data hilang, lalu User Admin default dibuat ulang via Seeder.
     */
    public function resetAll()
    {
        // Tabel-tabel sistem yang JANGAN dihapus
        $excludedTables = [
            'migrations',           // Wajib ada biar Laravel tau status migrasi
            'password_resets',      // Opsional
            'sessions',             // Agar user tidak langsung ter-logout paksa error
            'cache',                // Jika pakai driver database
            'personal_access_tokens' // Opsional
        ];

        try {
            Schema::disableForeignKeyConstraints();

            // 1. Ambil semua nama tabel di database
            $allTables = DB::connection()->getDoctrineSchemaManager()->listTableNames();

            foreach ($allTables as $table) {
                // Skip tabel yang dikecualikan
                if (in_array($table, $excludedTables)) {
                    continue;
                }

                // Kosongkan tabel (TRUNCATE lebih cepat dari DELETE dan mereset ID ke 1)
                DB::table($table)->truncate();
            }

            // 2. SEEDING ULANG (PENTING!)
            // Karena tabel 'users' dan 'roles' sudah kosong, kita harus isi ulang
            // agar Anda bisa login kembali. Pastikan Anda punya DatabaseSeeder.
            Artisan::call('db:seed', [
                '--class' => 'DatabaseSeeder',
                '--force' => true
            ]);

            Schema::enableForeignKeyConstraints();
            Artisan::call('cache:clear');

            return back()->with('success', 'Sistem berhasil di-reset total ke pengaturan pabrik.');
        } catch (\Exception $e) {
            Schema::enableForeignKeyConstraints();
            return back()->with('error', 'Gagal melakukan factory reset: ' . $e->getMessage());
        }
    }
}
