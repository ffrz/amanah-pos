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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Helpers\JsonResponseHelper;
use Ifsnop\Mysqldump\Mysqldump;
use App\Models\UserActivityLog; // Tambahkan ini

class DatabaseSettingsController extends Controller
{
    /**
     * @var UserActivityLogService
     */
    protected $userActivityLogService;

    // --- DAFTAR TABEL KONSTANTA ---
    protected const MASTER_TABLES = [
        'users',
        'acl_roles',
        'acl_permissions',
        'acl_model_has_roles',
        'acl_role_has_permissions',
        'acl_model_has_permissions',
        'products',
        'product_categories',
        'product_units',
        'product_images',
        'product_quantity_prices',
        'uoms',
        'customers',
        'suppliers',
        'finance_accounts',
        'finance_transaction_categories',
        'finance_transaction_tags',
        'operational_cost_categories',
        'tax_schemes',
        'settings',
        'cashier_terminals',
    ];

    protected const TRANSACTION_TABLES = [
        'sales_orders',
        'sales_order_details',
        'sales_order_payments',
        'sales_order_returns',
        'purchase_orders',
        'purchase_order_details',
        'purchase_order_payments',
        'purchase_order_returns',
        'stock_movements',
        'stock_adjustments',
        'stock_adjustment_details',
        'finance_transactions',
        'finance_transactions_has_tags',
        'operational_costs',
        'cashier_sessions',
        'cashier_session_transactions',
        'cashier_cash_drops',
        'customer_ledgers',
        'customer_wallet_transactions',
        'customer_wallet_trx_confirmations',
        'supplier_ledgers',
        'supplier_wallet_transactions',
        'user_activity_logs',
        'document_versions',
    ];

    public function __construct(UserActivityLogService $userActivityLogService)
    {
        $this->userActivityLogService = $userActivityLogService;
    }

    /**
     * Helper untuk memvalidasi password user yang sedang login.
     */
    protected function validatePassword(Request $request)
    {
        // Pastikan field 'password' ada dan diisi
        $request->validate(['password' => 'required|string']);

        $user = Auth::user();

        if (!Hash::check($request->password, $user->password)) {
            // Lempar error validasi yang akan ditangkap oleh Inertia/Vue
            throw ValidationException::withMessages([
                'password' => ['Password yang Anda masukkan salah. Silakan coba lagi.'],
            ]);
        }
    }

    // -------------------------------------------------------------------------
    // UTILITY METHOD
    // -------------------------------------------------------------------------

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
                'compress' => Mysqldump::NONE,
                'default-character-set' => Mysqldump::UTF8,
                'net_buffer_length' => 1000000,
                'no-data' => ($mode === 'structure_only'),
                'no-create-info' => ($mode === 'data_only'),
                'insert-ignore' => $safeMode,
                'add-drop-table' => true,
                'databases' => false,
            ];

            $dump = new Mysqldump($dsn, $db['username'], $db['password'], $dumpSettings);
            $dump->start($path);

            // Log Sukses
            $this->userActivityLogService->log(
                UserActivityLog::Category_Database,
                UserActivityLog::Name_Database_Backup,
                "Melakukan backup database mode '$mode' ke file '$filename'.",
                ['mode' => $mode, 'safe_mode' => $safeMode, 'filename' => $filename]
            );

            // Karena ini adalah download file, kita tetap menggunakan response download.
            return response()->download($path)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            // Log Gagal
            $this->userActivityLogService->log(
                UserActivityLog::Category_Database,
                UserActivityLog::Name_Database_Backup,
                "Gagal melakukan backup database mode '$mode'. Error: " . $e->getMessage(),
                ['mode' => $mode, 'error' => $e->getMessage()]
            );
            return JsonResponseHelper::error('Backup Gagal: ' . $e->getMessage(), 500);
        }
    }

    public function restore(Request $request)
    {
        // 1. Amankan data user yang sedang login sebelum validasi/restore.
        $currentUser = Auth::user();

        // Ambil SEMUA data user dari DB, bukan hanya ID dan password, 
        // untuk mengantisipasi INSERT jika user ini tidak ada di backup.
        $userModel = DB::table('users')->where('id', $currentUser->id)->first();

        // Cek jika user ini tiba-tiba hilang (misalnya dihapus user lain saat request ini diproses)
        if (!$userModel) {
            return JsonResponseHelper::error('User yang sedang login tidak ditemukan. Silakan logout dan login kembali.', 401);
        }

        // Simpan data dalam bentuk array, termasuk hash password saat ini
        $userDataToPreserve = (array)$userModel;


        // 2. Validasi Password dan File
        try {
            $this->validatePassword($request);
        } catch (ValidationException $e) {
            // Log Gagal Validasi Password
            $this->userActivityLogService->log(
                UserActivityLog::Category_Database,
                UserActivityLog::Name_Database_Restore,
                "Gagal melakukan restore. Password konfirmasi salah.",
                ['status' => 'Gagal Validasi Password']
            );
            throw $e; // Lempar kembali agar Inertia menangkap 422
        }

        $request->validate(['file' => 'required|file|max:102400']);

        $file = $request->file('file');
        $filename = $file->getClientOriginalName();
        $stream = fopen($file->getRealPath(), 'r');

        if (!$stream) {
            return JsonResponseHelper::error('Gagal membuka file backup.');
        }

        DB::disableQueryLog();
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        try {
            // --- Proses Restore SQL ---
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

            // 3. Injeksi ulang / Update data user yang sedang login
            $updatedRows = DB::table('users')
                ->where('id', $userDataToPreserve['id'])
                ->update([
                    'password' => $userDataToPreserve['password'],
                    'remember_token' => $userDataToPreserve['remember_token'] ?? null,
                ]);

            if ($updatedRows === 0) {
                // KASUS KRITIS: User tidak ditemukan di data backup
                unset($userDataToPreserve['created_at']);
                unset($userDataToPreserve['updated_at']);
                DB::table('users')->insert($userDataToPreserve);
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            // Log Sukses
            $this->userActivityLogService->log(
                UserActivityLog::Category_Database,
                UserActivityLog::Name_Database_Restore,
                "Database berhasil dipulihkan menggunakan file '$filename'. User ID: {$currentUser->id} berhasil diinjeksi ulang.",
                ['filename' => $filename, 'user_id_preserved' => $currentUser->id, 'action' => ($updatedRows === 0 ? 'INSERTED' : 'UPDATED')]
            );

            return JsonResponseHelper::success(null, 'Restore Selesai. Database berhasil dipulihkan.');
        } catch (\Exception $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            // Log Gagal
            $this->userActivityLogService->log(
                UserActivityLog::Category_Database,
                UserActivityLog::Name_Database_Restore,
                "Restore gagal dari file '$filename'. Error: " . $e->getMessage(),
                ['filename' => $filename, 'error' => $e->getMessage()]
            );
            return JsonResponseHelper::error('Restore Error: Terjadi kesalahan saat eksekusi SQL. ' . $e->getMessage(), 500);
        }
    }

    public function resetTransaction(Request $request)
    {
        $currentUser = Auth::user();

        // 1. Validasi Password
        try {
            $this->validatePassword($request);
        } catch (ValidationException $e) {
            // Log Gagal Validasi Password
            $this->userActivityLogService->log(
                UserActivityLog::Category_Database,
                UserActivityLog::Name_Database_ResetTransaction,
                "Gagal melakukan reset transaksional. Password konfirmasi salah.",
                ['status' => 'Gagal Validasi Password']
            );
            throw $e;
        }

        $tablesToTruncate = self::TRANSACTION_TABLES;

        try {
            Schema::disableForeignKeyConstraints();

            foreach ($tablesToTruncate as $table) {
                if (Schema::hasTable($table)) {
                    DB::table($table)->truncate();
                }
            }

            Schema::enableForeignKeyConstraints();
            Artisan::call('cache:clear');

            // Log Sukses
            $this->userActivityLogService->log(
                UserActivityLog::Category_Database,
                UserActivityLog::Name_Database_ResetTransaction,
                "Berhasil membersihkan data transaksional. Data master tetap utuh.",
                ['tables_truncated_count' => count($tablesToTruncate)]
            );

            return JsonResponseHelper::success(null, 'Data transaksional berhasil di-reset. Data master aman.');
        } catch (\Exception $e) {
            Schema::enableForeignKeyConstraints();
            // Log Gagal
            $this->userActivityLogService->log(
                UserActivityLog::Category_Database,
                UserActivityLog::Name_Database_ResetTransaction,
                "Gagal melakukan reset transaksional. Error: " . $e->getMessage(),
                ['error' => $e->getMessage()]
            );
            return JsonResponseHelper::error('Gagal mereset transaksi: ' . $e->getMessage(), 500);
        }
    }

    public function resetAll(Request $request)
    {
        // 1. Validasi Password
        try {
            $this->validatePassword($request);
        } catch (ValidationException $e) {
            // Log Gagal Validasi Password
            $this->userActivityLogService->log(
                UserActivityLog::Category_Database,
                UserActivityLog::Name_Database_ResetAll,
                "Gagal melakukan factory reset. Password konfirmasi salah.",
                ['status' => 'Gagal Validasi Password']
            );
            throw $e;
        }

        // Validasi Konfirmasi Teks (tambahan dari frontend)
        $request->validate(['confirm_text' => 'required|in:CONFIRM']);

        $allApplicationTables = array_merge(self::MASTER_TABLES, self::TRANSACTION_TABLES);

        try {
            Schema::disableForeignKeyConstraints();

            foreach ($allApplicationTables as $table) {
                if (Schema::hasTable($table)) {
                    DB::table($table)->truncate();
                }
            }

            // 2. SEEDING ULANG
            Artisan::call('db:seed', [
                '--class' => 'DatabaseSeeder', // Menggunakan DatabaseSeeder default
                '--force' => true
            ]);

            Schema::enableForeignKeyConstraints();
            Artisan::call('cache:clear');

            // Log Sukses
            $this->userActivityLogService->log(
                UserActivityLog::Category_Database,
                UserActivityLog::Name_Database_ResetAll,
                "Berhasil melakukan factory reset (reset total). Semua data aplikasi dihapus dan di-seed ulang.",
                ['tables_truncated_count' => count($allApplicationTables)]
            );

            return JsonResponseHelper::success(null, 'Sistem berhasil di-reset total ke pengaturan pabrik.');
        } catch (\Exception $e) {
            Schema::enableForeignKeyConstraints();
            // Log Gagal
            $this->userActivityLogService->log(
                UserActivityLog::Category_Database,
                UserActivityLog::Name_Database_ResetAll,
                "Gagal melakukan factory reset. Error: " . $e->getMessage(),
                ['error' => $e->getMessage()]
            );
            return JsonResponseHelper::error('Gagal melakukan factory reset: ' . $e->getMessage(), 500);
        }
    }
}
