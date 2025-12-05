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
use ZipArchive; // Belum dipakai, tapi mau di zip nantinya agar user gak bisa baca isi file
use Ifsnop\Mysqldump\Mysqldump;

class DatabaseSettingsController extends Controller
{
    /**
     * @var UserActivityLogService
     */
    protected $userActivityLogService;

    // --- DAFTAR TABEL KONSTANTA ---

    // 1. Tabel yang berisi data master dan konfigurasi.
    protected const MASTER_TABLES = [
        // Master User & ACL
        'users',
        'acl_roles',
        'acl_permissions',
        'acl_model_has_roles',
        'acl_role_has_permissions',
        'acl_model_has_permissions',

        // Master Produk
        'products',
        'product_categories',
        'product_units',
        'product_images',
        'product_quantity_prices',
        'uoms',

        // Master Pihak Ketiga
        'customers',
        'suppliers',

        // Master Keuangan & Config
        'finance_accounts',
        'finance_transaction_categories',
        'finance_transaction_tags',
        'operational_cost_categories',
        'tax_schemes',
        'settings',

        // Master Kasir
        'cashier_terminals',
    ];

    // 2. Tabel yang berisi data transaksi, log, dan pergerakan stok.
    protected const TRANSACTION_TABLES = [
        // Transaksi Penjualan & Pembelian
        'sales_orders',
        'sales_order_details',
        'sales_order_payments',
        'sales_order_returns',
        'purchase_orders',
        'purchase_order_details',
        'purchase_order_payments',
        'purchase_order_returns',

        // Transaksi Stok
        'stock_movements',
        'stock_adjustments',
        'stock_adjustment_details',

        // Transaksi Keuangan & Biaya
        'finance_transactions',
        'finance_transactions_has_tags',
        'operational_costs',

        // Transaksi Kasir
        'cashier_sessions',
        'cashier_session_transactions',
        'cashier_cash_drops',

        // Buku Besar & Wallet
        'customer_ledgers',
        'customer_wallet_transactions',
        'customer_wallet_trx_confirmations',
        'supplier_ledgers',
        'supplier_wallet_transactions',

        // Log & Sistem Non-Master
        'user_activity_logs',
        'document_versions',
    ];

    // Tabel Sistem yang WAJIB TIDAK DIHAPUS (bahkan di resetAll)
    protected const SYSTEM_TABLES = [
        'migrations',
        'sessions',
        'cache',
        'cache_locks',
        'password_reset_tokens',
        'personal_access_tokens',
        'failed_jobs',
        'jobs',
        'job_batches',
    ];

    // -------------------------------------------------------------------------
    // KODE CONSTRUCTOR, INDEX, BACKUP (dengan perbaikan namespace)
    // -------------------------------------------------------------------------

    public function __construct(UserActivityLogService $userActivityLogService)
    {
        $this->userActivityLogService = $userActivityLogService;
    }

    public function index()
    {
        return inertia('settings/database/Index');
    }

    public function backup(Request $request)
    {
        $mode = $request->input('mode', 'full');
        $safeMode = $request->boolean('safe_mode', false);

        $filename = 'backup_' . $mode . '_' . date('Y-m-d_H-i-s') . '.sql';
        $path = storage_path('app/backups/' . $filename);

        if (!file_exists(dirname($path))) mkdir(dirname($path), 0755, true);

        try {
            $db = config('database.connections.mysql');
            $dsn = "mysql:host={$db['host']};dbname={$db['database']};port={$db['port']}";

            $dumpSettings = [
                // Perbaikan: menggunakan Mysqldump::NONE karena sudah di-use di atas
                'compress' => Mysqldump::NONE,
                'default-character-set' => Mysqldump::UTF8,

                'net_buffer_length' => 1000000,

                'no-data' => ($mode === 'structure_only'),
                'no-create-info' => ($mode === 'data_only'),
                'insert-ignore' => $safeMode,

                'add-drop-table' => true,
                'databases' => false,
            ];

            // Perbaikan: menggunakan new Mysqldump() karena sudah di-use di atas
            $dump = new Mysqldump($dsn, $db['username'], $db['password'], $dumpSettings);
            $dump->start($path);

            return response()->download($path)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return back()->with('error', 'Backup Gagal: ' . $e->getMessage());
        }
    }

    public function restore(Request $request)
    {
        $request->validate(['file' => 'required|file|max:102400']);

        $file = $request->file('file');
        $stream = fopen($file->getRealPath(), 'r');

        if (!$stream) return back()->with('error', 'Gagal membuka file.');

        DB::disableQueryLog();
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        try {
            $queryBuffer = '';

            while (($line = fgets($stream)) !== false) {
                if (str_starts_with($line, '--') || str_starts_with($line, '/*') || trim($line) === '') {
                    continue;
                }

                $queryBuffer .= $line;

                if (trim($line) !== '' && str_ends_with(trim($line), ';')) {
                    DB::unprepared($queryBuffer);
                    $queryBuffer = '';
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
    
    // -------------------------------------------------------------------------
    // KODE RESET YANG DIUBAH (Sesuai permintaan Anda)
    // -------------------------------------------------------------------------

    /**
     * RESET TRANSAKSI SAJA
     * Menggunakan attribute TRANSACTION_TABLES.
     */
    public function resetTransaction()
    {
        // Menggunakan array konstan yang sudah didefinisikan
        $tablesToTruncate = self::TRANSACTION_TABLES;

        try {
            Schema::disableForeignKeyConstraints();

            foreach ($tablesToTruncate as $table) {
                if (Schema::hasTable($table)) {
                    DB::table($table)->truncate();
                }
            }

            // Tambahan: Reset quantity/stock di tabel master jika ada
            // DB::table('products')->update(['stock_quantity' => 0]); 

            Schema::enableForeignKeyConstraints();
            Artisan::call('cache:clear');

            return back()->with('success', 'Data transaksional berhasil di-reset. Data master aman.');
        } catch (\Exception $e) {
            Schema::enableForeignKeyConstraints();
            return back()->with('error', 'Gagal mereset transaksi: ' . $e->getMessage());
        }
    }

    /**
     * RESET TOTAL (FACTORY RESET)
     * Menggabungkan MASTER_TABLES dan TRANSACTION_TABLES.
     * Mengabaikan SYSTEM_TABLES.
     */
    public function resetAll()
    {
        // Gabungkan semua tabel Master dan Transaksi
        $allApplicationTables = array_merge(self::MASTER_TABLES, self::TRANSACTION_TABLES);

        try {
            Schema::disableForeignKeyConstraints();

            foreach ($allApplicationTables as $table) {
                // Tidak perlu ambil semua tabel di DB, cukup list manual ini.
                // Logika ini lebih aman karena hanya membersihkan tabel yang kita kenal.
                if (Schema::hasTable($table)) {
                    DB::table($table)->truncate();
                }
            }

            // 2. SEEDING ULANG (PENTING!)
            Artisan::call('db:seed', [
                '--class' => 'InitialDatabaseSeeder',
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
