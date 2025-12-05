<?php

/**
 * Proprietary Software / Perangkat Lunak Proprietary
 * Copyright (c) 2025 Fahmi Fauzi Rahman. All rights reserved.
 *
 * EN: Unauthorized use, copying, modification, atau distribution is prohibited.
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
use Illuminate\Support\Facades\File;
use App\Helpers\JsonResponseHelper;
use Ifsnop\Mysqldump\Mysqldump;
use App\Models\UserActivityLog;
// Import Model untuk memanggil static method generateOpeningSnapshot
use App\Models\StockMovement;
use App\Models\CustomerLedger;
use App\Models\SupplierLedger;
use App\Models\CustomerWalletTransaction;
use App\Models\SupplierWalletTransaction;
use App\Models\FinanceTransaction;
use ZipArchive;

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

    protected function validatePassword(Request $request)
    {
        $request->validate(['password' => 'required|string']);
        $user = Auth::user();
        if (!Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['Password yang Anda masukkan salah. Silakan coba lagi.'],
            ]);
        }
    }

    public function index()
    {
        return inertia('settings/database/Index');
    }

    public function backup(Request $request)
    {
        $mode = $request->input('mode', 'full');
        $safeMode = $request->boolean('safe_mode', false);

        $filenameBase = 'backup_' . $mode . '_' . date('Y-m-d_H-i-s');
        $sqlFilename = $filenameBase . '.sql';
        $zipFilename = $filenameBase . '.posdb';

        $tempDir = storage_path('app/backups/temp/' . $filenameBase);
        $sqlPath = $tempDir . '/' . $sqlFilename;
        $zipPath = storage_path('app/backups/' . $zipFilename);

        if (!File::exists($tempDir)) File::makeDirectory($tempDir, 0755, true);
        if (!File::exists(dirname($zipPath))) File::makeDirectory(dirname($zipPath), 0755, true);

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
            $dump->start($sqlPath);

            $zip = new ZipArchive;
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
                throw new \Exception('Gagal membuat file ZIP/POSDB.');
            }
            $zip->addFile($sqlPath, $sqlFilename);
            $zip->close();

            $this->userActivityLogService->log(
                UserActivityLog::Category_Database,
                UserActivityLog::Name_Database_Backup,
                "Melakukan backup database mode '$mode' ke file '$zipFilename'.",
                ['mode' => $mode, 'safe_mode' => $safeMode, 'filename' => $zipFilename]
            );

            return response()->download($zipPath)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            $this->userActivityLogService->log(
                UserActivityLog::Category_Database,
                UserActivityLog::Name_Database_Backup,
                "Gagal melakukan backup database mode '$mode'. Error: " . $e->getMessage(),
                ['mode' => $mode, 'error' => $e->getMessage()]
            );
            return JsonResponseHelper::error('Backup Gagal: ' . $e->getMessage(), 500);
        } finally {
            if (File::exists($tempDir)) {
                File::deleteDirectory($tempDir);
            }
        }
    }

    public function restore(Request $request)
    {
        $currentUser = Auth::user();
        $userModel = DB::table('users')->where('id', $currentUser->id)->first();

        if (!$userModel) {
            return JsonResponseHelper::error('User yang sedang login tidak ditemukan. Silakan logout dan login kembali.', 401);
        }

        $userDataToPreserve = (array)$userModel;

        try {
            $this->validatePassword($request);
        } catch (ValidationException $e) {
            $this->userActivityLogService->log(
                UserActivityLog::Category_Database,
                UserActivityLog::Name_Database_Restore,
                "Gagal melakukan restore. Password konfirmasi salah.",
                ['status' => 'Gagal Validasi Password']
            );
            throw $e;
        }

        $request->validate(['file' => 'required|file|max:102400']);

        $file = $request->file('file');
        $filename = $file->getClientOriginalName();
        $fileExtension = $file->getClientOriginalExtension();
        $tempRestoreDir = storage_path('app/temp_restore_' . time());
        $sqlPath = null;

        DB::disableQueryLog();
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        try {
            if (in_array(strtolower($fileExtension), ['posdb', 'zip'])) {
                $zip = new ZipArchive;
                if ($zip->open($file->getRealPath()) !== TRUE) {
                    throw new \Exception('Gagal membuka file ZIP/POSDB.');
                }

                if (!File::exists($tempRestoreDir)) File::makeDirectory($tempRestoreDir, 0755, true);

                $zip->extractTo($tempRestoreDir);
                $zip->close();

                $files = File::glob($tempRestoreDir . '/*.sql');
                if (empty($files)) {
                    throw new \Exception('Tidak ditemukan file .sql di dalam arsip yang diunggah.');
                }
                $sqlPath = $files[0];
            } elseif (strtolower($fileExtension) === 'sql') {
                $sqlPath = $file->getRealPath();
            } else {
                throw new \Exception('Format file tidak didukung. Harap gunakan .posdb, .zip, atau .sql.');
            }

            $stream = fopen($sqlPath, 'r');
            if (!$stream) throw new \Exception("Gagal membaca konten SQL dari file yang diekstrak.");

            // KOSONGKAN TABEL USERS SEBELUM RESTORE
            DB::table('users')->truncate();

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

            // Injeksi ulang user
            $updatedRows = DB::table('users')
                ->where('id', $userDataToPreserve['id'])
                ->update([
                    'password' => $userDataToPreserve['password'],
                    'remember_token' => $userDataToPreserve['remember_token'] ?? null,
                ]);

            if ($updatedRows === 0) {
                unset($userDataToPreserve['created_at']);
                unset($userDataToPreserve['updated_at']);
                $dataToInsert = $userDataToPreserve;
                DB::table('users')->insert($dataToInsert);
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            $this->userActivityLogService->log(
                UserActivityLog::Category_Database,
                UserActivityLog::Name_Database_Restore,
                "Database berhasil dipulihkan menggunakan file '$filename'. User ID: {$currentUser->id} berhasil diinjeksi ulang.",
                ['filename' => $filename, 'user_id_preserved' => $currentUser->id]
            );

            return JsonResponseHelper::success(null, 'Restore Selesai. Database berhasil dipulihkan.');
        } catch (\Exception $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            $this->userActivityLogService->log(
                UserActivityLog::Category_Database,
                UserActivityLog::Name_Database_Restore,
                "Restore gagal dari file '$filename'. Error: " . $e->getMessage(),
                ['filename' => $filename, 'error' => $e->getMessage()]
            );
            return JsonResponseHelper::error('Restore Error: Terjadi kesalahan saat eksekusi SQL. ' . $e->getMessage(), 500);
        } finally {
            if ($tempRestoreDir && File::exists($tempRestoreDir)) {
                File::deleteDirectory($tempRestoreDir);
            }
        }
    }

    public function resetTransaction(Request $request)
    {
        $currentUser = Auth::user();

        try {
            $this->validatePassword($request);
        } catch (ValidationException $e) {
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

            // 3. GENERATE OPENING BALANCE SNAPSHOT (Delegasi ke Model)
            // Pastikan Anda menerapkan method generateOpeningSnapshot() di masing-masing model ini
            // seperti contoh di FinanceTransaction.

            if (method_exists(StockMovement::class, 'generateOpeningSnapshot')) {
                StockMovement::generateOpeningSnapshot();
            }
            if (method_exists(CustomerLedger::class, 'generateOpeningSnapshot')) {
                CustomerLedger::generateOpeningSnapshot();
            }
            if (method_exists(SupplierLedger::class, 'generateOpeningSnapshot')) {
                SupplierLedger::generateOpeningSnapshot();
            }
            if (method_exists(CustomerWalletTransaction::class, 'generateOpeningSnapshot')) {
                CustomerWalletTransaction::generateOpeningSnapshot();
            }
            if (method_exists(SupplierWalletTransaction::class, 'generateOpeningSnapshot')) {
                SupplierWalletTransaction::generateOpeningSnapshot();
            }
            if (method_exists(FinanceTransaction::class, 'generateOpeningSnapshot')) {
                FinanceTransaction::generateOpeningSnapshot();
            }

            Schema::enableForeignKeyConstraints();
            Artisan::call('cache:clear');

            $this->userActivityLogService->log(
                UserActivityLog::Category_Database,
                UserActivityLog::Name_Database_ResetTransaction,
                "Berhasil membersihkan data transaksional dan membuat snapshot saldo awal.",
                ['tables_truncated_count' => count($tablesToTruncate)]
            );

            return JsonResponseHelper::success(null, 'Data transaksional berhasil di-reset. Data master aman dan saldo awal telah dibuat.');
        } catch (\Exception $e) {
            Schema::enableForeignKeyConstraints();
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
        try {
            $this->validatePassword($request);
        } catch (ValidationException $e) {
            $this->userActivityLogService->log(
                UserActivityLog::Category_Database,
                UserActivityLog::Name_Database_ResetAll,
                "Gagal melakukan factory reset. Password konfirmasi salah.",
                ['status' => 'Gagal Validasi Password']
            );
            throw $e;
        }

        $request->validate(['confirm_text' => 'required|in:CONFIRM']);

        $allApplicationTables = array_merge(self::MASTER_TABLES, self::TRANSACTION_TABLES);

        try {
            Schema::disableForeignKeyConstraints();

            foreach ($allApplicationTables as $table) {
                if (Schema::hasTable($table)) {
                    DB::table($table)->truncate();
                }
            }

            Artisan::call('db:seed', [
                '--class' => 'InitialDatabaseSeeder',
                '--force' => true
            ]);

            Schema::enableForeignKeyConstraints();
            Artisan::call('cache:clear');

            $this->userActivityLogService->log(
                UserActivityLog::Category_Database,
                UserActivityLog::Name_Database_ResetAll,
                "Berhasil melakukan factory reset (reset total).",
                ['tables_truncated_count' => count($allApplicationTables)]
            );

            return JsonResponseHelper::success(null, 'Sistem berhasil di-reset total ke pengaturan pabrik.');
        } catch (\Exception $e) {
            Schema::enableForeignKeyConstraints();
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
