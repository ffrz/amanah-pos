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
use App\Models\FinanceAccount;
use App\Models\FinanceTransaction;
use App\Models\UserActivityLog;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class FinanceAccountService
{
    public function __construct(
        protected UserActivityLogService $userActivityLogService,
        protected DocumentVersionService $documentVersionService,
    ) {}

    public function getTotalActiveAccountBalance()
    {
        return FinanceAccount::where('active', true)->sum('balance');
    }

    public function find(int $id): FinanceAccount
    {
        return FinanceAccount::findOrFail($id);
    }

    public function duplicate(int $id): FinanceAccount
    {
        return $this->find($id)->replicate();
    }

    public function findOrCreate($id): FinanceAccount
    {
        return $id ? $this->find($id) : new FinanceAccount(['active' => true]);
    }

    /**
     * Menyimpan atau memperbarui Akun Keuangan dan menangani penyesuaian saldo awal/perubahan.
     *
     * @param int|null $id ID akun untuk diperbarui, atau null untuk yang baru.
     * @param array $validatedData Data yang sudah divalidasi dari request.
     * @return FinanceAccount
     */
    public function save(FinanceAccount $item, array $data): FinanceAccount
    {
        $isNew = !$item->id;
        $oldBalance = $item->balance ?? 0;
        $oldData = $item->toArray();

        $item->fill($data);

        return DB::transaction(function () use ($item, $oldData, $isNew, $oldBalance) {
            $now = Carbon::now();

            $item->save();

            $newBalance = $item->balance;
            $balanceDiff = $newBalance - $oldBalance;

            // Penanganan Transaksi Penyesuaian Saldo (Adjustment)
            if ($isNew && $newBalance != 0.) {
                // Saldo awal untuk akun baru yang tidak nol
                FinanceTransaction::create([
                    'account_id' => $item->id,
                    'datetime'   => $now,
                    'type'       => FinanceTransaction::Type_Adjustment,
                    'amount'     => $newBalance,
                    'notes'      => 'Saldo awal akun',
                ]);
            } elseif (!$isNew && abs($balanceDiff) > 0.001) { // Gunakan toleransi float
                // Penyesuaian saldo untuk akun yang sudah ada
                FinanceTransaction::create([
                    'account_id' => $item->id,
                    'datetime'   => $now,
                    'type'       => FinanceTransaction::Type_Adjustment,
                    'amount'     => $balanceDiff,
                    'notes'      => 'Penyesuaian saldo akun (dari ' . format_number($oldBalance) . ' ke ' . format_number($newBalance) . ')',
                ]);
            }

            $this->documentVersionService->createVersion($item);

            if ($isNew) {
                $this->userActivityLogService->log(
                    UserActivityLog::Category_FinanceAccount,
                    UserActivityLog::Name_FinanceAccount_Create,
                    "Akun kas $item->id telah dibuat.",
                    [
                        'formatter' => 'finance-account',
                        'data' => $item->toArray(),
                    ]
                );
            } else {
                $this->userActivityLogService->log(
                    UserActivityLog::Category_FinanceAccount,
                    UserActivityLog::Name_FinanceAccount_Update,
                    "Akun kas $item->id telah diperbarui.",
                    [
                        'formatter' => 'finance-account',
                        'new_data' => $item->toArray(),
                        'old_data' => $oldData,
                    ]
                );
            }

            return $item;
        });
    }

    /**
     * Menghapus Akun Keuangan setelah memeriksa keterkaitannya.
     *
     * @param int $id
     * @return FinanceAccount
     * @throws \Exception
     */
    public function delete(FinanceAccount $item): FinanceAccount
    {
        if ($item->isUsedInTransaction()) {
            throw new BusinessRuleViolationException('Akun tidak dapat dihapus karena sudah digunakan di transaksi!');
        }

        return DB::transaction(function () use ($item) {
            $item->delete();

            $this->documentVersionService->createDeletedVersion($item);

            $this->userActivityLogService->log(
                UserActivityLog::Category_FinanceAccount,
                UserActivityLog::Name_FinanceAccount_Delete,
                "Akun kas $item->id telah dihapus.",
                [
                    'formatter' => 'finance-account',
                    'data' => $item->toArray(),
                ]
            );

            return $item;
        });
    }

    /**
     * Mengambil daftar akun keuangan dengan fitur pencarian dan paginasi.
     *
     * @param array $options Data request yang sudah divalidasi.
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getData(array $options)
    {
        $q = FinanceAccount::query();

        $filter    = $options['filter'];
        $orderBy   = $options['order_by'];
        $orderType = $options['order_type'];
        $perPage   = $options['per_page'];

        // Pencarian (Search)
        if (!empty($filter['search'])) {
            $q->where(function ($q) use ($filter) {
                $search = '%' . $filter['search'] . '%';
                $q->where('name', 'like', $search)
                    ->orWhere('bank', 'like', $search)
                    ->orWhere('holder', 'like', $search)
                    ->orWhere('number', 'like', $search)
                    ->orWhere('notes', 'like', $search);
            });
        }

        // Filter Status
        if (!empty($filter['status']) && ($filter['status'] === 'active' || $filter['status'] === 'inactive')) {
            $q->where('active', '=', $filter['status'] === 'active');
        }

        // Filter Type
        if (!empty($filter['type']) && $filter['type'] !== 'all') {
            $q->where('type', '=', $filter['type']);
        }

        $q->orderBy($orderBy, $orderType);

        return $q->paginate($perPage)->withQueryString();
    }

    public function getFinanceAccounts(): Collection
    {
        return FinanceAccount::where('active', '=', true)
            ->where(function ($query) {
                $query->where('type', '=', FinanceAccount::Type_Cash)
                    ->orWhere('type', '=', FinanceAccount::Type_Bank)
                    ->orWhere('type', '=', FinanceAccount::Type_PettyCash);
            })
            ->where('show_in_pos_payment', '=', true)
            ->orderBy('name')
            ->get();
    }
}
