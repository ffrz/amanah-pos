<?php

namespace Modules\Admin\Services;

use App\Models\FinanceAccount;
use App\Models\FinanceTransaction;
use App\Models\UserActivityLog;
use Carbon\Carbon;
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

    public function find($id): FinanceAccount
    {
        return FinanceAccount::findOrFail($id);
    }

    public function duplicate($id): FinanceAccount
    {
        $item = $this->find($id);
        $item->id = null;
        return $item;
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
    public function save(FinanceAccount $item, array $validatedData): FinanceAccount
    {
        DB::beginTransaction();

        try {
            $isNew = !$item->exists;
            $now = Carbon::now();
            $oldBalance = $item->balance ?? 0;
            $oldData = $item->toArray();

            $item->fill($validatedData);
            $item->save(); // 1. UPDATE/INSERT FinanceAccount

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
                ]); // 2. INSERT FinanceTransaction
            } elseif (!$isNew && abs($balanceDiff) > 0.001) { // Gunakan toleransi float
                // Penyesuaian saldo untuk akun yang sudah ada
                FinanceTransaction::create([
                    'account_id' => $item->id,
                    'datetime'   => $now,
                    'type'       => FinanceTransaction::Type_Adjustment,
                    'amount'     => $balanceDiff,
                    'notes'      => 'Penyesuaian saldo akun (dari ' . format_number($oldBalance) . ' ke ' . format_number($newBalance) . ')',
                ]); // 2. INSERT FinanceTransaction
            }

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

            $this->documentVersionService->createVersion($item);

            DB::commit();
            return $item;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
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
            throw new \Exception('Akun tidak dapat dihapus karena sudah digunakan di transaksi!');
        }

        try {
            DB::beginTransaction();

            $item->delete();

            $this->userActivityLogService->log(
                UserActivityLog::Category_FinanceAccount,
                UserActivityLog::Name_FinanceAccount_Delete,
                "Akun kas $item->id telah dihapus.",
                [
                    'formatter' => 'finance-account',
                    'data' => $item->toArray(),
                ]
            );

            $this->documentVersionService->createDeletedVersion($item);

            DB::commit();

            return $item;
        } catch (\Exception $ex) {
            DB::rollBack();
            throw $ex;
        }
    }

    /**
     * Mengambil daftar akun keuangan dengan fitur pencarian dan paginasi.
     *
     * @param array $validatedData Data request yang sudah divalidasi.
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getData(array $validatedData)
    {
        $q = FinanceAccount::query();

        $filter = $validatedData['filter'];
        $orderBy = $validatedData['order_by'];
        $orderType = $validatedData['order_type'];
        $perPage = $validatedData['per_page'];

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
}
