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

use App\Models\FinanceAccount;
use App\Models\FinanceTransaction;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;

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
        return $id ? $this->find($id) : new FinanceTransaction();
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
                $q->orWhere('notes', 'like', '%' . $filter['search'] . '%');
            });
        }

        if (!empty($filter['year']) && $filter['year'] !== 'all') {
            $q->whereYear('datetime', $filter['year']);

            if (!empty($filter['month']) && $filter['month'] !== 'all') {
                $q->whereMonth('datetime', $filter['month']);
            }
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
}
