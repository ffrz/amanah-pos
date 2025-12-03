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
use App\Models\Supplier;
use App\Models\SupplierWalletTransaction;
use App\Models\FinanceTransaction;
use App\Models\UserActivityLog;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class SupplierWalletTransactionService
{

    public function __construct(
        protected FinanceTransactionService $financeTransactionService,
        protected UserActivityLogService $userActivityLogService,
        protected DocumentVersionService $documentVersionService,
    ) {}

    public function find(int $id, $rels = ['supplier', 'financeAccount']): SupplierWalletTransaction
    {
        return SupplierWalletTransaction::with($rels)->findOrFail($id);
    }

    public function findOrCreate($id): SupplierWalletTransaction
    {
        return $id ? $this->find($id) : new SupplierWalletTransaction([
            'datetime' => now()
        ]);
    }

    public function getData(array $options): LengthAwarePaginator
    {
        $filter = $options['filter'];

        $q = SupplierWalletTransaction::with([
            'supplier' => function ($query) {
                $query->select('id', 'code', 'name');
            },
            'financeAccount' => function ($query) {
                $query->select('id', 'name', 'bank', 'holder', 'number');
            },
        ]);

        if (!empty($filter['search'])) {
            $q->where(function ($q) use ($filter) {
                $q->orWhere('code', 'like', "%" . $filter['search'] . "%");
                $q->orWhere('notes', 'like', '%' . $filter['search'] . '%');
            });
        }

        if (!empty($filter['start_date'])) {
            $q->where('datetime', '>=', $filter['start_date']);
        }

        if (!empty($filter['end_date'])) {
            $q->where('datetime', '<=', $filter['end_date']);
        }


        if (!empty($filter['supplier_id']) && $filter['supplier_id'] != 'all') {
            $q->where('supplier_id', '=', $filter['supplier_id']);
        }

        $q->orderBy($options['order_by'], $options['order_type']);

        return $q->paginate($options['per_page']);
    }

    public function save(SupplierWalletTransaction $item, array $validated, $imageFile = null): SupplierWalletTransaction
    {
        // 1. Tetapkan jumlah (negatif / positif) berdasarkan jenis transaksi wallet
        // Pembayaran PO dan Penarikan akan mengurangi saldo wallet supplier
        if (
            in_array($validated['type'], [
                SupplierWalletTransaction::Type_PurchaseOrderPayment,
                SupplierWalletTransaction::Type_Withdrawal,
            ])
        ) {
            $validated['amount'] *= -1;
        }

        return DB::transaction(function () use ($item, $validated, $imageFile) {
            // 2. Upload image jika ada
            if ($imageFile) {
                $validated['image_path'] = ImageUploaderHelper::uploadAndResize(
                    $imageFile,
                    'supplier-wallet-transactions'
                );
            }
            unset($validated['image']);

            // 3. Simpan Transaksi Wallet & Update Saldo Supplier
            $item = $this->handleTransaction($validated);

            // 4. Integrasi Keuangan (Finance)
            // Hanya dijalankan untuk Deposit/Withdrawal yang melibatkan akun kas
            if (
                in_array($validated['type'], [
                    SupplierWalletTransaction::Type_Deposit,
                    SupplierWalletTransaction::Type_Withdrawal,
                ]) && !empty($validated['finance_account_id'])
            ) {
                // [LOGIC FIX]:
                // Deposit ke Supplier (Amount +) = Uang Keluar dari Kas (Expense)
                // Penarikan dari Supplier (Amount -) = Uang Masuk ke Kas (Income)

                $isExpense = $validated['amount'] >= 0;

                $this->financeTransactionService->handleTransaction([
                    'datetime'   => $validated['datetime'],
                    'account_id' => $validated['finance_account_id'],
                    // Kita balik nilai amount-nya untuk finance:
                    // Jika Deposit (+), kirim (-) agar mengurangi kas.
                    // Jika Withdrawal (-), kirim (+) agar menambah kas.
                    'amount'     => -$validated['amount'],
                    'type'       => $isExpense ? FinanceTransaction::Type_Expense : FinanceTransaction::Type_Income,
                    'notes'      => 'Transaksi wallet supplier ' . $item->supplier->code . ' Ref: ' . $item->code,
                    'ref_type'   => FinanceTransaction::RefType_SupplierWalletTransaction,
                    'ref_id'     => $item->id,
                ]);
            }

            // 5. Versioning
            $this->documentVersionService->createVersion($item);

            // 6. Logging
            $this->userActivityLogService->log(
                UserActivityLog::Category_SupplierWallet,
                UserActivityLog::Name_SupplierWalletTransaction_Create,
                "Transaksi deposit supplier $item->code telah dibuat.",
                [
                    'formatter' => 'supplier-wallet-transaction',
                    'data' => $item->toArray(),
                ]
            );

            return $item;
        });
    }

    public function delete(SupplierWalletTransaction $item): SupplierWalletTransaction
    {
        if ($item->ref_type) {
            throw new BusinessRuleViolationException('Rekaman tidak dapat dihapus karena berrelasi dengan transaksi lain.');
        }

        return DB::transaction(function () use ($item) {
            // restore supplier wallet_balance
            $this->addToWalletBalance($item->supplier, -$item->amount);

            // Jika transaksi menyentuh kas, kembalikan saldo kas
            if (in_array($item->type, [
                SupplierWalletTransaction::Type_Deposit,
                SupplierWalletTransaction::Type_Withdrawal
            ]) && $item->finance_account_id) {
                $this->financeTransactionService->reverseTransaction($item->id, FinanceTransaction::RefType_SupplierWalletTransaction);
            }

            ImageUploaderHelper::deleteImage($item->image_path);

            $item->delete();

            $this->documentVersionService->createVersion($item);

            $this->userActivityLogService->log(
                UserActivityLog::Category_SupplierWallet,
                UserActivityLog::Name_SupplierWalletTransaction_Delete,
                "Transaksi deposit supplier $item->code telah dihapus.",
                [
                    'formatter' => 'supplier-wallet-transaction',
                    'data' => $item->toArray(),
                ]
            );

            return $item;
        });
    }

    public function adjustBalance(array $data)
    {
        $supplier = Supplier::findOrFail($data['supplier_id']);

        return DB::transaction(function () use ($supplier, $data) {
            $item = $this->handleTransaction([
                'supplier_id' => $supplier->id,
                'datetime'    => Carbon::now(),
                'type'        => SupplierWalletTransaction::Type_Adjustment,
                'amount'      => $data['new_wallet_balance'] - $supplier->wallet_balance,
                'notes'       => $data['notes'],
            ]);

            $this->documentVersionService->createVersion($item);

            $this->userActivityLogService->log(
                UserActivityLog::Category_SupplierWallet,
                UserActivityLog::Name_SupplierWalletTransaction_Create,
                "Transaksi penyesuaian saldo deposit supplier $item->code telah dibuat.",
                [
                    'formatter' => 'supplier-wallet-transaction',
                    'data' => $item->toArray(),
                ]
            );

            return $item;
        });
    }

    // Helper functions

    protected function addToWalletBalance(Supplier $supplier, $amount): void
    {
        $lockedSupplier = Supplier::where('id', $supplier->id)->lockForUpdate()->firstOrFail();
        $lockedSupplier->wallet_balance += $amount;
        $lockedSupplier->save();
    }

    // WARNING: Tidak mendukung pengeditan transaksi!!
    public function handleTransaction(array $newData)
    {
        // Perbarui saldo akun baru
        $account = Supplier::findOrFail($newData['supplier_id']);

        // jika negatif dan jumlah melebihi saldo, tolak
        if ($newData['amount'] < 0 && abs($newData['amount']) > $account->wallet_balance) {
            throw new BusinessRuleViolationException('Jumlah penarikan melebihi saldo!');
        }

        $this->addToWalletBalance($account, $newData['amount']);

        // Buat transaksi baru
        return SupplierWalletTransaction::create(
            [
                'ref_id' => $newData['ref_id'] ?? null,
                'ref_type' => $newData['ref_type'] ?? null,
                'supplier_id' => $newData['supplier_id'],
                'finance_account_id' => $newData['finance_account_id'] ?? null,
                'datetime' => $newData['datetime'],
                'amount' => $newData['amount'],
                'type' => $newData['type'],
                'notes' => $newData['notes'],
                'image_path' => $newData['image_path'] ?? null,
            ]
        );
    }

    public function reverseTransaction($ref_id, $ref_type)
    {
        $trx = SupplierWalletTransaction::where('ref_id', $ref_id)
            ->where('ref_type', $ref_type)
            ->first();

        if ($trx) {
            $this->addToWalletBalance($trx->supplier, -$trx->amount);
            $trx->delete();
        }

        return  $trx;
    }
}
