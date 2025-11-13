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

use App\Exceptions\ModelNotModifiedException;
use App\Models\FinanceTransactionCategory;
use App\Models\UserActivityLog;

use Illuminate\Support\Facades\DB;
use Modules\Admin\Services\UserActivityLogService;

class FinanceTransactionCategoryService
{
    /**
     * @var UserActivityLogService
     */
    protected UserActivityLogService $userActivityLogService;

    public function __construct(UserActivityLogService $userActivityLogService)
    {
        $this->userActivityLogService = $userActivityLogService;
    }

    /**
     * Mengambil data kategori biaya operasional dengan paginasi dan filter.
     * Logika utama untuk query getData() tetap berada di sini.
     *
     * @param array $options
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getData(array $options)
    {
        $filter = $options['filter'];

        $q = FinanceTransactionCategory::query()
            ->select(['id', 'name', 'description']);

        if (!empty($filter['search'])) {
            $q->where(function ($q) use ($filter) {
                $q->where('name', 'like', '%' . $filter['search'] . '%')
                    ->orWhere('description', 'like', '%' . $filter['search'] . '%');
            });
        }

        $q->orderBy($options['order_by'], $options['order_type']);

        return $q->paginate($options['per_page'] ?? 10);
    }

    /**
     * Mencari kategori berdasarkan ID.
     *
     * @param int $id
     * @return FinanceTransactionCategory|null
     */
    public function find($id): ?FinanceTransactionCategory
    {
        return FinanceTransactionCategory::findOrFail($id);
    }

    public function findOrCreate($id): FinanceTransactionCategory
    {
        return $id ? $this->find($id) : new FinanceTransactionCategory();
    }

    /**
     * Membuat model duplikat (menggunakan instance yang sudah ada di service).
     *
     * @param int $id
     * @return FinanceTransactionCategory
     */
    public function duplicate($id): FinanceTransactionCategory
    {
        return $this->find($id)->replicate();
    }

    /**
     * Menyimpan kategori biaya operasional baru atau yang sudah ada (termasuk fill, transaksi, dan logging).
     *
     * @param FinanceTransactionCategory $item Model yang akan disimpan.
     * @param array $validatedData Data yang divalidasi dari request.
     * @return mixed
     * @throws \Exception Jika terjadi kesalahan saat transaksi DB.
     */
    public function save(FinanceTransactionCategory $item, array $data): FinanceTransactionCategory
    {
        $isNew = empty($data['id']);

        $oldData = $item->toArray();

        $item->fill($data);

        if (empty($item->getDirty())) {
            throw new ModelNotModifiedException();
        }

        return DB::transaction(function () use ($isNew, $oldData, $item) {
            $item->save();

            if ($isNew) {
                $this->userActivityLogService->log(
                    UserActivityLog::Category_FinanceTransactionCategory,
                    UserActivityLog::Name_FinanceTransactionCategory_Create,
                    "Kategori transaksi ID: $item->id telah dibuat.",
                    [
                        'formatter' => 'finance-transaction-category',
                        'new_data'  => $item->getAttributes(),
                    ]
                );
            } else {
                $this->userActivityLogService->log(
                    UserActivityLog::Category_FinanceTransactionCategory,
                    UserActivityLog::Name_FinanceTransactionCategory_Update,
                    "Kategori transaksi ID: $item->id telah diperbarui.",
                    [
                        'formatter' => 'finance-transaction-category',
                        'new_data'  => $item->getAttributes(),
                        'old_data'  => $oldData,
                    ]
                );
            }

            return $item;
        });
    }

    /**
     * Menghapus kategori biaya operasional dan mencatat aktivitas.
     *
     * @param FinanceTransactionCategory $item
     * @return mixed
     * @throws \Exception Jika terjadi kesalahan saat transaksi DB.
     */
    public function delete(FinanceTransactionCategory $item)
    {
        return DB::transaction(function () use ($item) {
            $deleted = $item->delete();

            $this->userActivityLogService->log(
                UserActivityLog::Category_FinanceTransactionCategory,
                UserActivityLog::Name_FinanceTransactionCategory_Delete,
                "Kategori transaksi ID: $item->id telah dihapus.",
                [
                    'formatter' => 'finance-transaction-category',
                    'new_data'  => $item->toArray(),
                ]
            );

            return $deleted;
        });
    }
}
