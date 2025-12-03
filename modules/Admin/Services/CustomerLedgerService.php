<?php

/**
 * Proprietary Software / Perangkat Lunak Proprietary
 * Copyright (c) 2025 Fahmi Fauzi Rahman. All rights reserved.
 */

namespace Modules\Admin\Services;

use App\Exceptions\BusinessRuleViolationException;
use App\Helpers\ImageUploaderHelper;
use App\Models\Customer;
use App\Models\CustomerLedger;
use App\Models\FinanceTransaction;
use App\Models\UserActivityLog;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CustomerLedgerService
{
    public function __construct(
        protected FinanceTransactionService $financeTransactionService,
        protected UserActivityLogService $userActivityLogService,
    ) {}

    public function find(int $id): CustomerLedger
    {
        return CustomerLedger::with(['customer', 'ref'])->findOrFail($id);
    }

    public function getData(array $options): LengthAwarePaginator
    {
        // ... (Code sama seperti sebelumnya untuk filter & pagination) ...
        $filter = $options['filter'];
        $q = CustomerLedger::with(['customer', 'ref']);

        if (!empty($filter['search'])) {
            $q->where('code', 'like', "%" . $filter['search'] . "%")
                ->orWhere('notes', 'like', '%' . $filter['search'] . '%');
        }
        if (!empty($filter['customer_id']) && $filter['customer_id'] != 'all') {
            $q->where('customer_id', $filter['customer_id']);
        }
        $q->orderBy($options['order_by'] ?? 'datetime', $options['order_type'] ?? 'desc');
        return $q->paginate($options['per_page']);
    }

    /**
     * Mencatat transaksi ledger manual (Tambah/Kurang Utang).
     * Contoh: Input Saldo Awal, Input Bonus, Hapus Buku (Write-off).
     */
    public function save(array $validated, $imageFile = null): CustomerLedger
    {
        return DB::transaction(function () use ($validated, $imageFile) {

            // 1. Standarisasi Input (Positif/Negatif)
            // Logika: Invoice/Saldo Awal = Positif (Piutang Nambah)
            // Payment/Retur/Diskon = Negatif (Piutang Berkurang)
            $amount = abs($validated['amount']);

            if (in_array($validated['type'], [
                CustomerLedger::Type_Payment,
                CustomerLedger::Type_Return,
                // Jika user memilih tipe adjustment tertentu yang sifatnya mengurangi
            ])) {
                $amount *= -1;
            }

            // Override amount dengan hasil kalkulasi
            $validated['amount'] = $amount;

            // 2. Upload Image
            if ($imageFile) {
                $validated['image_path'] = ImageUploaderHelper::uploadAndResize(
                    $imageFile,
                    'customer-ledgers'
                );
            }

            // 3. Simpan Transaksi Ledger
            $item = $this->handleTransaction($validated);

            // 4. Integrasi ke Keuangan (Opsional, jika user mencentang 'Masuk Kas')
            // Contoh: Mencatat pembayaran utang lama secara tunai
            if (!empty($validated['finance_account_id'])) {
                $this->financeTransactionService->handleTransaction([
                    'datetime'   => $validated['datetime'],
                    'account_id' => $validated['finance_account_id'],
                    // Jika Ledger (-) Piutang Berkurang -> Berarti Uang Masuk (+) Income
                    // Jika Ledger (+) Piutang Nambah -> Berarti Uang Keluar (Pinjaman) (-) Expense
                    'amount'     => $amount * -1,
                    'type'       => ($amount * -1) >= 0 ? FinanceTransaction::Type_Income : FinanceTransaction::Type_Expense,
                    'notes'      => 'Transaksi Utang / Piutang Pelanggan ' . $item->customer->name . ' Ref: ' . $item->code,
                    'ref_type'   => 'customer_ledger',
                    'ref_id'     => $item->id,
                ]);
            }

            $this->userActivityLogService->log(
                UserActivityLog::Category_CustomerLedger,
                UserActivityLog::Name_CustomerLedger_Create,
                "Mencatat utang / piutang pelanggan {$item->customer->name}",
                ['data' => $item->toArray()]
            );

            return $item;
        });
    }

    /**
     * Fitur Penyesuaian Saldo (Opname Piutang).
     * User input "Saldo Seharusnya", sistem hitung selisihnya.
     */
    public function adjustBalance(array $data)
    {
        $customer = Customer::findOrFail($data['customer_id']);

        return DB::transaction(function () use ($customer, $data) {
            // Hitung selisih: Saldo Baru - Saldo Lama
            // Contoh: Utang di sistem 1jt. Utang aktual 800rb.
            // Selisih = 800rb - 1jt = -200rb (Kurangi piutang 200rb)
            $diff = $data['new_balance'] - $customer->balance;

            if ($diff == 0) {
                throw new BusinessRuleViolationException("Saldo sudah sesuai, tidak ada perubahan.");
            }

            $item = $this->handleTransaction([
                'customer_id' => $customer->id,
                'datetime'    => now(),
                'type'        => CustomerLedger::Type_Adjustment,
                'amount'      => $diff,
                'notes'       => $data['notes'] ?? 'Penyesuaian saldo piutang (Stock Opname)',
            ]);

            $this->userActivityLogService->log(
                UserActivityLog::Category_CustomerLedger,
                UserActivityLog::Name_CustomerLedger_Adjust,
                "Penyesuaian saldo utang / piutang pelanggan {$customer->name} menjadi " . number_format($data['new_balance']),
                ['data' => $item->toArray()]
            );

            return $item;
        });
    }

    public function delete(CustomerLedger $item): CustomerLedger
    {
        // Validasi: Jangan hapus ledger otomatis dari Order
        if ($item->ref_type) {
            throw new BusinessRuleViolationException('Tidak dapat menghapus ledger yang berasal dari transaksi otomatis.');
        }

        return DB::transaction(function () use ($item) {
            // 1. Reverse Saldo Customer (Kebalikan dari amount)
            $this->updateCustomerDebtBalance($item->customer_id, -$item->amount);

            // 2. Hapus Image
            ImageUploaderHelper::deleteImage($item->image_path);

            // 3. Hapus relasi keuangan jika ada
            // Logic reverse finance transaction jika diperlukan
            // ...

            $item->delete();

            $this->userActivityLogService->log(
                UserActivityLog::Category_CustomerLedger,
                UserActivityLog::Name_CustomerLedger_Delete,
                "Menghapus utang / piutang pelanggan #{$item->code}",
                ['data' => $item->toArray()]
            );

            return $item;
        });
    }

    /**
     * Core Logic: Insert DB & Update Master Balance
     * Hanya menerima data matang.
     */
    public function handleTransaction(array $data): CustomerLedger
    {
        // 1. Update Saldo Master & Ambil Saldo Akhir
        $newMasterBalance = $this->updateCustomerDebtBalance($data['customer_id'], $data['amount']);

        // 2. Create Ledger Record
        return CustomerLedger::create([
            'customer_id' => $data['customer_id'],
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

    protected function updateCustomerDebtBalance(int $customerId, float $amountChange): float
    {
        $customer = Customer::where('id', $customerId)->lockForUpdate()->firstOrFail();
        $customer->balance += $amountChange;
        $customer->save();

        return $customer->balance;
    }
}
