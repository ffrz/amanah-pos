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
use App\Models\Customer;
use App\Models\CustomerWalletTransaction;
use App\Models\FinanceTransaction;
use App\Models\UserActivityLog;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CustomerWalletTransactionService
{

    public function __construct(
        protected FinanceTransactionService $financeTransactionService,
        protected UserActivityLogService $userActivityLogService,
        protected DocumentVersionService $documentVersionService,
    ) {}

    public function find(int $id): CustomerWalletTransaction
    {
        return CustomerWalletTransaction::with(['customer'])->findOrFail($id);
    }

    public function findOrCreate($id): CustomerWalletTransaction
    {
        return $id ? $this->find($id) : new CustomerWalletTransaction([
            'datetime' => now()
        ]);
    }

    public function getData(array $options): LengthAwarePaginator
    {
        $filter = $options['filter'];

        $q = CustomerWalletTransaction::with([
            'customer' => function ($query) {
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


        if (!empty($filter['customer_id']) && $filter['customer_id'] != 'all') {
            $q->where('customer_id', '=', $filter['customer_id']);
        }

        $q->orderBy($options['order_by'], $options['order_type']);

        return $q->paginate($options['per_page']);
    }

    public function save(CustomerWalletTransaction $item, array $validated, $imageFile = null): CustomerWalletTransaction
    {
        // tetapkan jumlah (negatif / positif) berdasakran jenis transaksi
        if (
            in_array($validated['type'], [
                CustomerWalletTransaction::Type_SalesOrderPayment,
                CustomerWalletTransaction::Type_Withdrawal,
            ])
        ) {
            $validated['amount'] *= -1;
        }

        return DB::transaction(function () use ($item, $validated, $imageFile) {
            // upload image jika ada
            if ($imageFile) {
                $validated['image_path'] = ImageUploaderHelper::uploadAndResize(
                    $imageFile,
                    'customer-wallet-transactions'
                );
            }
            unset($validated['image']);

            // tangani transaksi
            $item = $this->handleTransaction($validated);

            // hanya digunakan ketika mempengaruhi akun kas
            if (
                in_array($validated['type'], [
                    CustomerWalletTransaction::Type_Deposit,
                    CustomerWalletTransaction::Type_Withdrawal,
                ]) && $validated['finance_account_id']
            ) {
                $this->financeTransactionService->handleTransaction([
                    'datetime' => $validated['datetime'],
                    'account_id' => $validated['finance_account_id'],
                    'amount' => $validated['amount'],
                    'type' => $validated['amount'] >= 0 ? FinanceTransaction::Type_Income : FinanceTransaction::Type_Expense,
                    'notes' => 'Transaksi wallet customer ' . $item->customer->code . ' Ref: ' . $item->code,
                    'ref_type' => FinanceTransaction::RefType_CustomerWalletTransaction,
                    'ref_id' => $item->id,
                ]);
            }

            $this->documentVersionService->createVersion($item);

            $this->userActivityLogService->log(
                UserActivityLog::Category_CustomerWallet,
                UserActivityLog::Name_CustomerWalletTransaction_Create,
                "Transaksi wallet pelanggan $item->code telah dibuat.",
                [
                    'formatter' => 'customer-wallet-transaction',
                    'data' => $item->toArray(),
                ]
            );

            return $item;
        });
    }

    public function delete(CustomerWalletTransaction $item): CustomerWalletTransaction
    {
        if ($item->ref_type) {
            throw new BusinessRuleViolationException('Rekaman tidak dapat dihapus karena berrelasi dengan transaksi lain.');
        }

        return DB::transaction(function () use ($item) {
            // restore customer wallet_balance
            $this->addToWalletBalance($item->customer, -$item->amount);

            // Jika transaksi menyentuh kas, kembalikan saldo kas
            if (in_array($item->type, [
                CustomerWalletTransaction::Type_Deposit,
                CustomerWalletTransaction::Type_Withdrawal
            ]) && $item->finance_account_id) {
                $this->financeTransactionService->reverseTransaction($item->id, FinanceTransaction::RefType_CustomerWalletTransaction);
            }

            ImageUploaderHelper::deleteImage($item->image_path);

            $item->delete();

            $this->documentVersionService->createVersion($item);

            $this->userActivityLogService->log(
                UserActivityLog::Category_CustomerWallet,
                UserActivityLog::Name_CustomerWalletTransaction_Delete,
                "Transaksi wallet pelanggan $item->code telah dihapus.",
                [
                    'formatter' => 'customer-wallet-transaction',
                    'data' => $item->toArray(),
                ]
            );

            return $item;
        });
    }

    public function adjustBalance(array $data)
    {
        $customer = Customer::findOrFail($data['customer_id']);

        return DB::transaction(function () use ($customer, $data) {
            $item = $this->handleTransaction([
                'customer_id' => $customer->id,
                'datetime'    => Carbon::now(),
                'type'        => CustomerWalletTransaction::Type_Adjustment,
                'amount'      => $data['new_wallet_balance'] - $customer->wallet_balance,
                'notes'       => $data['notes'],
            ]);

            $this->documentVersionService->createVersion($item);

            $this->userActivityLogService->log(
                UserActivityLog::Category_CustomerWallet,
                UserActivityLog::Name_CustomerWalletTransaction_Create,
                "Transaksi penyesuaian saldo wallet pelanggan $item->code telah dibuat.",
                [
                    'formatter' => 'customer-wallet-transaction',
                    'data' => $item->toArray(),
                ]
            );

            return $item;
        });
    }

    // Helper functions

    protected function addToWalletBalance(Customer $customer, $amount): void
    {
        $lockedCustomer = Customer::where('id', $customer->id)->lockForUpdate()->firstOrFail();
        $lockedCustomer->wallet_balance += $amount;
        $lockedCustomer->save();
    }

    // WARNING: Tidak mendukung pengeditan transaksi!!
    public function handleTransaction(array $newData)
    {
        // Perbarui saldo akun baru
        $account = Customer::findOrFail($newData['customer_id']);

        // jika negatif dan jumlah melebihi saldo, tolak
        if ($newData['amount'] < 0 && abs($newData['amount']) > $account->wallet_balance) {
            throw new BusinessRuleViolationException('Jumlah penarikan melebihi saldo!');
        }

        $this->addToWalletBalance($account, $newData['amount']);

        // Buat transaksi baru
        return CustomerWalletTransaction::create(
            [
                'ref_id' => $newData['ref_id'] ?? null,
                'ref_type' => $newData['ref_type'] ?? null,
                'customer_id' => $newData['customer_id'],
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
        $trx = CustomerWalletTransaction::where('ref_id', $ref_id)
            ->where('ref_type', $ref_type)
            ->first();

        if ($trx) {
            $this->addToWalletBalance($trx->customer, -$trx->amount);
            $trx->delete();
        }

        return  $trx;
    }
}
