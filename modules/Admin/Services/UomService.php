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
use App\Models\Uom;
use App\Models\UserActivityLog;

use Illuminate\Support\Facades\DB;
use Modules\Admin\Services\UserActivityLogService;

class UomService
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

        $q = Uom::query()
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
     * Mencari satuan berdasarkan ID.
     *
     * @param int $id
     * @return Uom|null
     */
    public function find($id): ?Uom
    {
        return Uom::findOrFail($id);
    }

    public function findOrCreate($id): Uom
    {
        return $id ? $this->find($id) : new Uom();
    }

    /**
     * Membuat model duplikat (menggunakan instance yang sudah ada di service).
     *
     * @param int $id
     * @return Uom
     */
    public function duplicate($id): Uom
    {
        return $this->find($id)->replicate();
    }

    /**
     * Menyimpan satuan baru atau yang sudah ada (termasuk fill, transaksi, dan logging).
     *
     * @param Uom $item Model yang akan disimpan.
     * @param array $validatedData Data yang divalidasi dari request.
     * @return mixed
     * @throws \Exception Jika terjadi kesalahan saat transaksi DB.
     */
    public function save(Uom $item, array $data): Uom
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
                    UserActivityLog::Category_Uom,
                    UserActivityLog::Name_Uom_Create,
                    "Satuan #$item->id:$item->name telah dibuat.",
                    [
                        'formatter' => 'uom',
                        'new_data'  => $item->getAttributes(),
                    ]
                );
            } else {
                $this->userActivityLogService->log(
                    UserActivityLog::Category_Uom,
                    UserActivityLog::Name_Uom_Update,
                    "Satuan #$item->id:$item->name telah diperbarui.",
                    [
                        'formatter' => 'uom',
                        'new_data'  => $item->getAttributes(),
                        'old_data'  => $oldData,
                    ]
                );
            }

            return $item;
        });
    }

    /**
     * Menghapus satuan dan mencatat aktivitas.
     *
     * @param Uom $item
     * @return mixed
     * @throws \Exception Jika terjadi kesalahan saat transaksi DB.
     */
    public function delete(Uom $item)
    {
        return DB::transaction(function () use ($item) {
            $deleted = $item->delete();

            $this->userActivityLogService->log(
                UserActivityLog::Category_Uom,
                UserActivityLog::Name_Uom_Delete,
                "Satuan #$item->id:$item->name telah dihapus.",
                [
                    'formatter' => 'uom',
                    'new_data'  => $item->toArray(),
                ]
            );

            return $deleted;
        });
    }
}
