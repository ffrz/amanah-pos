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
use App\Models\UserActivityLog;
use Carbon\Carbon;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class FinanceTransactionService
{
    public function __construct(
        protected UserActivityLogService $userActivityLogService,
        protected DocumentVersionService $documentVersionService,
    ) {}

    public function find(int $id): FinanceTransaction
    {
        return FinanceTransaction::with(['account', 'creator', 'updater'])
            ->findOrFail($id);
    }

    public function findOrCreate($id): FinanceTransaction
    {
        return $id ? $this->find($id) : new FinanceTransaction(['datetime' => Carbon::now()]);
    }

    public function addToBalance(FinanceAccount $account, $amount): void
    {
        $lockedAccount = FinanceAccount::where('id', $account->id)->lockForUpdate()->firstOrFail();
        $lockedAccount->balance += $amount;
        $lockedAccount->save();
    }

    public function handleTransaction(array $newData, array $oldData = [])
    {
        // Cek apakah ada data lama dan akun keuangan berubah
        if (isset($oldData['account_id'])) {
            // Kembalikan saldo akun lama
            $oldAccount = FinanceAccount::findOrFail($oldData['account_id']);
            $this->addToBalance($oldAccount, -$oldData['amount']);
        }

        // Jika tidak ada akun finansial baru, hapus transaksi lama dan berhenti
        if (empty($newData['account_id'])) {
            if (isset($oldData['ref_id'])) {
                FinanceTransaction::where('ref_id', $oldData['ref_id'])
                    ->where('ref_type', $oldData['ref_type'])
                    ->delete();
            }
            return;
        }

        // Perbarui saldo akun baru
        $account = FinanceAccount::findOrFail($newData['account_id']);
        $this->addToBalance($account, $newData['amount']);

        // Buat transaksi baru atau perbarui transaksi
        return FinanceTransaction::updateOrCreate(
            [
                'ref_id' => $newData['ref_id'],
                'ref_type' => $newData['ref_type'],
            ],
            [
                'account_id' => $newData['account_id'],
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
        $trx = FinanceTransaction::where('ref_id', $ref_id)
            ->where('ref_type', $ref_type)
            ->first();

        if ($trx) {
            $account = $trx->account;
            $this->addToBalance($account, -1 * $trx->amount);
            $trx->delete();
        }

        return $trx;
    }

    public function getData(array $options): LengthAwarePaginator
    {
        $filter = $options['filter'];

        $q = FinanceTransaction::with('account');

        if (!empty($filter['search'])) {
            $q->where(function ($q) use ($filter) {
                $q->orWhere('code', 'like', "%" . $filter['search'] . "%");
                $q->orWhere('notes', 'like', '%' . $filter['search'] . '%');
            });
        }

        if (!empty($filter['start_date'])) {
            $q->whereDate('datetime', '>=', $filter['start_date']);
        }

        if (!empty($filter['end_date'])) {
            $q->where('datetime', '<=', $filter['end_date']);
        }


        if (!empty($filter['type']) && $filter['type'] !== 'all') {
            $q->where('type', $filter['type']);
        }

        if (!empty($filter['account_id']) && $filter['account_id'] !== 'all') {
            $q->where('account_id', $filter['account_id']);
        }

        if (!empty($filter['user_id']) && $filter['user_id'] !== 'all') {
            $q->where('created_by', $filter['user_id']);
        }

        if (!empty($filter['from_datetime'])) {
            $start = Carbon::parse($filter['from_datetime']);
            $end = empty($filter['to_datetime']) ? Carbon::now() : Carbon::parse($filter['to_datetime']);
            $q->whereBetween('created_at', [$start, $end]);
        }

        $q->orderBy($options['order_by'], $options['order_type']);

        return $q->paginate($options['per_page'])->withQueryString();
    }

    public function delete(FinanceTransaction $item): FinanceTransaction
    {
        if ($item->ref_type && $item->ref_type != FinanceTransaction::RefType_FinanceTransaction) {
            throw new BusinessRuleViolationException("
                Transaksi #$item->id tidak dapat dihapus karena berkaitan dengan transaksi lainnya.
                Ref type: $item->ref_type. Ref ID: $item->ref_id", 403);
        }

        return DB::transaction(function () use ($item) {
            if ($item->type === FinanceTransaction::Type_Transfer && $item->ref_type === FinanceTransaction::RefType_FinanceTransaction && $item->ref_id) {
                $pair = FinanceTransaction::find($item->ref_id);
                if ($pair) {
                    $this->deleteTransaction($pair);

                    $this->documentVersionService->createDeletedVersion($item);

                    $this->userActivityLogService->log(
                        UserActivityLog::Category_FinanceTransaction,
                        UserActivityLog::Name_FinanceTransaction_Delete,
                        "Transaksi $pair->code telah dihapus",
                        [
                            "data" => $pair->toArray(),
                            "formatter" => "finance-transaction",
                        ]
                    );
                }
            }

            $this->deleteTransaction($item);

            $this->documentVersionService->createDeletedVersion($item);

            $this->userActivityLogService->log(
                UserActivityLog::Category_FinanceTransaction,
                UserActivityLog::Name_FinanceTransaction_Delete,
                "Transaksi $item->code telah dihapus",
                [
                    "data" => $item->toArray(),
                    "formatter" => "finance-transaction",
                ]
            );

            return $item;
        });
    }

    private function deleteTransaction($trx)
    {
        $this->addToBalance($trx->account, -$trx->amount);
        $trx->delete();
        ImageUploaderHelper::deleteImage($trx->image_path);
    }

    public function save(array $validated, $imageFile)
    {
        $item = null;
        $newlyUploadedImagePath = null;

        try {
            DB::beginTransaction();

            if ($imageFile) {
                $newlyUploadedImagePath = ImageUploaderHelper::uploadAndResize(
                    $imageFile,
                    'finance-transactions'
                );
                $validated['image_path'] = $newlyUploadedImagePath;
                unset($validated['image']);
            }

            if ($validated['type'] === FinanceTransaction::Type_Transfer && !empty($validated['to_account_id'])) {
                $items = $this->createTransferTransactions($validated); // pair

                foreach ($items as $item) {
                    $this->documentVersionService->createVersion($item);

                    $code = $item->code;

                    $this->userActivityLogService->log(
                        UserActivityLog::Category_FinanceTransaction,
                        UserActivityLog::Name_FinanceTransaction_Create,
                        "Transaksi $code telah dibuat",
                        [
                            "data" => $item->toArray(),
                            "formatter" => "finance-transaction",
                        ]
                    );
                }
            } else {
                $item = $this->createSingleTransaction($validated);

                $this->documentVersionService->createVersion($item);

                $this->userActivityLogService->log(
                    UserActivityLog::Category_FinanceTransaction,
                    UserActivityLog::Name_FinanceTransaction_Create,
                    "Transaksi $item->code telah dibuat",
                    [
                        "data" => $item->toArray(),
                        "formatter" => "finance-transaction",
                    ]
                );
            }

            DB::commit();

            return $item;
        } catch (Exception $ex) {
            DB::rollBack();

            if ($newlyUploadedImagePath) {
                ImageUploaderHelper::deleteImage($newlyUploadedImagePath);
            }

            throw $ex;
        }
    }

    private function createSingleTransaction(array $validated): FinanceTransaction
    {
        // Handle transaksi biasa (income / expense)
        $amount = $validated['amount'];
        if ($validated['type'] === FinanceTransaction::Type_Expense) {
            $amount = -$amount;
        }

        $transaction = new FinanceTransaction();
        $transaction->fill([
            'account_id'  => $validated['account_id'],
            'datetime'    => $validated['datetime'],
            'type'        => $validated['type'],
            'amount'      => $amount,
            'notes'       => $validated['notes'],
            'image_path'  => $validated['image_path'],
        ]);
        $transaction->save();

        $this->addToBalance($transaction->account, $transaction->amount);

        return $transaction;
    }

    private function createTransferTransactions(array $validated): array
    {
        $fromTransaction = new FinanceTransaction();
        $fromTransaction->fill([
            'account_id' => $validated['account_id'],
            'datetime'   => $validated['datetime'],
            'type'       => FinanceTransaction::Type_Transfer,
            'amount'     => -$validated['amount'],
            'notes'      => 'Transfer ke akun #' . $validated['to_account_id'] . '. ' . $validated['notes'],
            'image_path' => $validated['image_path'],
        ]);
        $fromTransaction->save();

        // Update saldo akun asal
        $this->addToBalance($fromTransaction->account, $fromTransaction->amount);

        // Transaksi masuk ke akun tujuan
        $toTransaction = new FinanceTransaction();
        $toTransaction->fill([
            'account_id' => $validated['to_account_id'],
            'datetime'   => $validated['datetime'],
            'type'       => FinanceTransaction::Type_Transfer,
            'amount'     => $validated['amount'], // masuk
            'notes'      => 'Transfer dari akun #' . $validated['account_id'] . '. ' . $validated['notes'],
            'image_path' => $validated['image_path'],
        ]);
        $toTransaction->save();

        // Update saldo akun tujuan
        $this->addToBalance($toTransaction->account, $toTransaction->amount);

        // Link untuk keperluan delete
        $fromTransaction->ref_type = FinanceTransaction::RefType_FinanceTransaction;
        $fromTransaction->ref_id = $toTransaction->id;
        $fromTransaction->save();

        $toTransaction->ref_type = FinanceTransaction::RefType_FinanceTransaction;
        $toTransaction->ref_id = $fromTransaction->id;
        $toTransaction->save();

        return [$fromTransaction, $toTransaction];
    }
}
