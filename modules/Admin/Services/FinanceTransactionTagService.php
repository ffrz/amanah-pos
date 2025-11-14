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
use App\Models\FinanceTransactionTag;
use App\Models\UserActivityLog;

use Illuminate\Support\Facades\DB;
use Modules\Admin\Services\UserActivityLogService;

class FinanceTransactionTagService
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

        $q = FinanceTransactionTag::query()
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
     * @return FinanceTransactionTag|null
     */
    public function find($id): ?FinanceTransactionTag
    {
        return FinanceTransactionTag::findOrFail($id);
    }

    public function findOrCreate($id): FinanceTransactionTag
    {
        return $id ? $this->find($id) : new FinanceTransactionTag();
    }

    /**
     * Membuat model duplikat (menggunakan instance yang sudah ada di service).
     *
     * @param int $id
     * @return FinanceTransactionTag
     */
    public function duplicate($id): FinanceTransactionTag
    {
        return $this->find($id)->replicate();
    }

    /**
     * Menyimpan kategori biaya operasional baru atau yang sudah ada (termasuk fill, transaksi, dan logging).
     *
     * @param FinanceTransactionTag $item Model yang akan disimpan.
     * @param array $validatedData Data yang divalidasi dari request.
     * @return mixed
     * @throws \Exception Jika terjadi kesalahan saat transaksi DB.
     */
    public function save(FinanceTransactionTag $item, array $data): FinanceTransactionTag
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
                    UserActivityLog::Category_FinanceTransactionTag,
                    UserActivityLog::Name_FinanceTransactionTag_Create,
                    "Label transaksi ID: $item->id telah dibuat.",
                    [
                        'formatter' => 'finance-transaction-tag',
                        'new_data'  => $item->getAttributes(),
                    ]
                );
            } else {
                $this->userActivityLogService->log(
                    UserActivityLog::Category_FinanceTransactionTag,
                    UserActivityLog::Name_FinanceTransactionTag_Update,
                    "Label transaksi ID: $item->id telah diperbarui.",
                    [
                        'formatter' => 'finance-transaction-tag',
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
     * @param FinanceTransactionTag $item
     * @return mixed
     * @throws \Exception Jika terjadi kesalahan saat transaksi DB.
     */
    public function delete(FinanceTransactionTag $item)
    {
        return DB::transaction(function () use ($item) {
            $deleted = $item->delete();

            $this->userActivityLogService->log(
                UserActivityLog::Category_FinanceTransactionTag,
                UserActivityLog::Name_FinanceTransactionTag_Delete,
                "Label transaksi ID: $item->id telah dihapus.",
                [
                    'formatter' => 'finance-transaction-tag',
                    'new_data'  => $item->toArray(),
                ]
            );

            return $deleted;
        });
    }
}
