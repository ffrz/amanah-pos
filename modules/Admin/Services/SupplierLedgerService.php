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

            // 1. Standarisasi Input (Positif/Negatif)
            // Logika Utang Dagang (AP):
            // Bill/Opening Balance = Positif (Utang Kita Nambah)
            // Payment/Return = Negatif (Utang Kita Berkurang)
            $amount = abs($validated['amount']);

            if (in_array($validated['type'], [
                SupplierLedger::Type_Payment,
                SupplierLedger::Type_Return
            ])) {
                $amount *= -1;
            }

            // Override amount dengan hasil kalkulasi
            $validated['amount'] = $amount;

            // 2. Upload Image
            if ($imageFile) {
                $validated['image_path'] = ImageUploaderHelper::uploadAndResize(
                    $imageFile,
                    'supplier-ledgers'
                );
            }

            // 3. Simpan Transaksi Ledger
            $item = $this->handleTransaction($validated);

            // 4. Integrasi ke Keuangan (Opsional)
            // Contoh: Kita bayar utang lama ke supplier pakai uang kas kantor
            if (!empty($validated['finance_account_id'])) {
                // Jika Ledger (-) Utang Berkurang -> Berarti Kita Keluar Uang (-) Expense
                // Jika Ledger (+) Utang Nambah -> Berarti Kita Terima Uang/Pinjaman (+) Income (Jarang, tapi mungkin)

                // Di FinanceTransactionService, biasanya amount diharapkan signed (+/-) atau
                // unsigned dengan type explicit. Mari kita asumsikan logic generic:
                // Kita kirim amount yang merepresentasikan pergerakan KAS.

                // Jika Utang Berkurang ($amount negatif), Kas Berkurang (juga negatif).
                $cashAmount = $amount;

                $this->financeTransactionService->handleTransaction([
                    'datetime'   => $validated['datetime'],
                    'account_id' => $validated['finance_account_id'],
                    'amount'     => $cashAmount,
                    'type'       => $cashAmount >= 0 ? FinanceTransaction::Type_Income : FinanceTransaction::Type_Expense,
                    'notes'      => 'Transaksi utang / piutang pemasok ' . $item->supplier->name . ' Ref: ' . $item->code,
                    'ref_type'   => 'supplier_ledger', // Tambahkan ke model FinanceTransaction
                    'ref_id'     => $item->id,
                ]);
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
            $this->updateSupplierDebtBalance($item->supplier_id, -$item->amount);

            // 2. Hapus Image
            ImageUploaderHelper::deleteImage($item->image_path);

            // 3. Hapus relasi keuangan jika ada
            // Logic reverse finance transaction...

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
            'finance_account_id' => $data['finance_account_id'],
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
}
