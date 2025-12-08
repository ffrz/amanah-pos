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

namespace Modules\Admin\Services;

use App\Exceptions\BusinessRuleViolationException;
use App\Helpers\ImageUploaderHelper;
use App\Models\FinanceAccount;
use App\Models\FinanceTransaction;
use App\Models\Supplier;
use App\Models\SupplierLedger;
use App\Models\UserActivityLog;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class SupplierLedgerService
{
    public function __construct(
        protected FinanceTransactionService $financeTransactionService,
        protected UserActivityLogService $userActivityLogService,
    ) {}

    public function find(int $id): SupplierLedger
    {
        return SupplierLedger::with(['supplier', 'ref'])->findOrFail($id);
    }

    public function getData(array $options): LengthAwarePaginator
    {
        $filter = $options['filter'];
        $q = SupplierLedger::with(['supplier', 'ref']);

        if (!empty($filter['search'])) {
            $q->where(function ($query) use ($filter) {
                $query->where('code', 'like', "%" . $filter['search'] . "%")
                    ->orWhere('notes', 'like', '%' . $filter['search'] . '%');
            });
        }

        if (!empty($filter['start_date'])) {
            $q->where('datetime', '>=', $filter['start_date']);
        }
        if (!empty($filter['end_date'])) {
            $q->where('datetime', '<=', $filter['end_date']);
        }

        if (!empty($filter['supplier_id']) && $filter['supplier_id'] != 'all') {
            $q->where('supplier_id', $filter['supplier_id']);
        }

        $q->orderBy($options['order_by'] ?? 'datetime', $options['order_type'] ?? 'desc');

        return $q->paginate($options['per_page']);
    }

    /**
     * Mencatat transaksi ledger manual (Tambah/Kurang Utang ke Supplier).
     * Contoh: Input Saldo Awal Utang, Catat Pembayaran Manual, dll.
     */
    public function save(array $validated, $imageFile = null): SupplierLedger
    {
        return DB::transaction(function () use ($validated, $imageFile) {
            // 1. Hitung Amount untuk Ledger (Sesuai Logika Net Balance)
            // Bill = Negatif (Nambah Utang), Payment = Positif (Kurang Utang)
            $rawAmount = abs($validated['amount']);
            $ledgerAmount = $rawAmount * SupplierLedger::getMultiplier($validated['type']);

            $validated['amount'] = $ledgerAmount;

            // Upload Image
            if ($imageFile) {
                $validated['image_path'] = ImageUploaderHelper::uploadAndResize(
                    $imageFile,
                    'supplier-ledgers'
                );
            }

            // 2. Simpan ke Supplier Ledger
            $item = $this->handleTransaction($validated);

            // 3. Integrasi ke Keuangan (Cash Flow)
            // HANYA JIKA ada akun kas yang dipilih
            if (!empty($validated['finance_account_id'])) {

                $financeType = null;
                $cashFlowAmount = 0;

                // Mapping Logika Ledger -> Logika Kas
                switch ($validated['type']) {
                    case SupplierLedger::Type_Payment:
                        // Kita Bayar Supplier -> Uang Keluar (Expense) -> Negatif
                        $financeType = FinanceTransaction::Type_Expense;
                        $cashFlowAmount = -1 * $rawAmount;
                        break;

                    case SupplierLedger::Type_Refund:
                        // Supplier Balikin Uang -> Uang Masuk (Income) -> Positif
                        $financeType = FinanceTransaction::Type_Income;
                        $cashFlowAmount = $rawAmount;
                        break;

                    case SupplierLedger::Type_Bill:
                        // Kasus Jarang: Input Tagihan langsung pilih Kas (Beli Tunai)
                        // Uang Keluar (Expense) -> Negatif
                        $financeType = FinanceTransaction::Type_Expense;
                        $cashFlowAmount = -1 * $rawAmount;
                        break;

                    // Opening Balance, Adjustment, Debit Note biasanya NON-CASH (bypass)
                    // Jadi default null agar tidak tercatat di kas
                    default:
                        $financeType = null;
                }

                // Eksekusi jika tipe keuangannya valid
                if ($financeType) {
                    $this->financeTransactionService->handleTransaction([
                        'datetime'   => $validated['datetime'],
                        'account_id' => $validated['finance_account_id'] ?? null,
                        'amount'     => $cashFlowAmount, // Gunakan nilai yang sudah disesuaikan arahnya
                        'type'       => $financeType,    // Gunakan tipe yang eksplisit
                        'notes'      => 'Transaksi pemasok ' . $item->supplier->name . ' Ref: ' . $item->code,
                        'ref_type'   => 'supplier_ledger',
                        'ref_id'     => $item->id,
                    ]);
                }
            }

            $this->userActivityLogService->log(
                UserActivityLog::Category_SupplierLedger,
                UserActivityLog::Name_SupplierLedger_Create,
                "Mencatat ledger manual pemasok {$item->supplier->name}",
                ['data' => $item->toArray()]
            );

            return $item;
        });
    }

    /**
     * Fitur Penyesuaian Saldo (Opname Utang).
     * User input "Saldo Utang Seharusnya", sistem hitung selisih.
     */
    public function adjustBalance(array $data)
    {
        $supplier = Supplier::findOrFail($data['supplier_id']);

        return DB::transaction(function () use ($supplier, $data) {
            // Hitung selisih: Saldo Baru - Saldo Lama
            // Contoh: Di sistem utang 1jt. Tagihan fisik supplier bilang 1.2jt.
            // Selisih = 1.2jt - 1jt = +200rb (Tambah utang 200rb)
            $diff = $data['new_balance'] - $supplier->balance;

            if ($diff == 0) {
                throw new BusinessRuleViolationException("Saldo sudah sesuai, tidak ada perubahan.");
            }

            $item = $this->handleTransaction([
                'supplier_id' => $supplier->id,
                'datetime'    => now(),
                'type'        => SupplierLedger::Type_Adjustment,
                'amount'      => $diff,
                'notes'       => $data['notes'] ?? 'Penyesuaian saldo utang',
            ]);

            $this->userActivityLogService->log(
                UserActivityLog::Category_SupplierLedger,
                UserActivityLog::Name_SupplierLedger_Adjust,
                "Penyesuaian saldo utang / piutang pemasok {$supplier->name} menjadi " . number_format($data['new_balance']),
                ['data' => $item->toArray()]
            );

            return $item;
        });
    }

    public function delete(SupplierLedger $item): SupplierLedger
    {
        // Validasi: Jangan hapus ledger otomatis dari Purchase Order
        if ($item->ref_type) {
            throw new BusinessRuleViolationException('Tidak dapat menghapus ledger yang berasal dari transaksi otomatis (PO).');
        }

        return DB::transaction(function () use ($item) {
            // 1. Reverse Saldo Supplier (Kebalikan dari amount)
            // Mengembalikan posisi utang ke sebelum transaksi ini ada
            $this->updateSupplierDebtBalance($item->supplier_id, -$item->amount);

            // 2. Hapus Image
            if ($item->image_path) {
                ImageUploaderHelper::deleteImage($item->image_path);
            }

            // 3. Hapus relasi keuangan jika ada (CRITICAL FIX)
            // Cari transaksi kas yang terhubung dengan ledger ini
            $financeTx = FinanceTransaction::where('ref_type', 'supplier_ledger')
                ->where('ref_id', $item->id)
                ->first();

            // Jika ditemukan, hapus transaksi keuangannya agar saldo kas kembali normal
            if ($financeTx) {
                // PENTING: Pastikan Model FinanceTransaction memiliki Observer/Event
                // yang otomatis mengupdate saldo FinanceAccount saat di-delete.
                $financeTx->delete();

                // TODO: CEK Barangkali Terbalik
                FinanceAccount::incrementBalance($financeTx->account_id, -$financeTx->amount);
            }

            // 4. Hapus Item Ledger
            $item->delete();

            $this->userActivityLogService->log(
                UserActivityLog::Category_SupplierLedger,
                UserActivityLog::Name_SupplierLedger_Delete,
                "Menghapus utang / piutang pemasok #{$item->code}",
                ['data' => $item->toArray()]
            );

            return $item;
        });
    }

    /**
     * Core Logic: Insert DB & Update Master Balance
     */
    public function handleTransaction(array $data): SupplierLedger
    {
        // 1. Update Saldo Master & Ambil Saldo Akhir
        $newMasterBalance = $this->updateSupplierDebtBalance($data['supplier_id'], $data['amount']);

        // 2. Create Ledger Record
        return SupplierLedger::create([
            'supplier_id' => $data['supplier_id'],
            'finance_account_id' => $data['finance_account_id'] ?? null,
            'datetime'    => $data['datetime'],
            'type'        => $data['type'],
            'amount'      => $data['amount'],
            'running_balance' => $newMasterBalance, // Snapshot
            'ref_type'    => $data['ref_type'] ?? null,
            'ref_id'      => $data['ref_id'] ?? null,
            'notes'       => $data['notes'] ?? '',
            'image_path'  => $data['image_path'] ?? null,
        ]);
    }

    protected function updateSupplierDebtBalance(int $supplierId, float $amountChange): float
    {
        $supplier = Supplier::where('id', $supplierId)->lockForUpdate()->firstOrFail();

        // debt_balance (Utang Kita) bertambah atau berkurang
        $supplier->balance += $amountChange;
        $supplier->save();

        return $supplier->balance;
    }

    public function deleteByRef(string $refType, int $refId)
    {
        $items = SupplierLedger::where('ref_type', $refType)
            ->where('ref_id', $refId)
            ->get();

        foreach ($items as $item) {
            $item->ref_type = null;
            $this->delete($item);
        }
    }
}
