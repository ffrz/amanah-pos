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
use App\Models\ProductBrand;
use App\Models\UserActivityLog;

use Illuminate\Support\Facades\DB;
use Modules\Admin\Services\UserActivityLogService;

class ProductBrandService
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

        $q = ProductBrand::query()
            ->select(['id', 'name', 'active']);

        if (!empty($filter['search'])) {
            $q->where(function ($q) use ($filter) {
                $q->where('name', 'like', '%' . $filter['search'] . '%');
            });
        }

        if (!empty($filter['status']) && ($filter['status'] == 'active' || $filter['status'] == 'inactive')) {
            $q->where('active', '=', $filter['status'] == 'active' ? true : false);
        }

        $q->orderBy($options['order_by'], $options['order_type']);

        return $q->paginate($options['per_page'] ?? 10);
    }

    /**
     * Mencari Merk Produk berdasarkan ID.
     *
     * @param int $id
     * @return ProductBrand|null
     */
    public function find($id): ?ProductBrand
    {
        return ProductBrand::findOrFail($id);
    }

    public function findOrCreate($id): ProductBrand
    {
        return $id ? $this->find($id) : new ProductBrand([
            'active' => true,
        ]);
    }

    /**
     * Membuat model duplikat (menggunakan instance yang sudah ada di service).
     *
     * @param int $id
     * @return ProductBrand
     */
    public function duplicate($id): ProductBrand
    {
        return $this->find($id)->replicate();
    }

    /**
     * Menyimpan Merk Produk baru atau yang sudah ada (termasuk fill, transaksi, dan logging).
     *
     * @param ProductBrand $item Model yang akan disimpan.
     * @param array $validatedData Data yang divalidasi dari request.
     * @return mixed
     * @throws \Exception Jika terjadi kesalahan saat transaksi DB.
     */
    public function save(ProductBrand $item, array $data): ProductBrand
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
                    UserActivityLog::Category_ProductBrand,
                    UserActivityLog::Name_ProductBrand_Create,
                    "Merk Produk #$item->id:$item->name telah dibuat.",
                    [
                        'formatter' => 'product-brand',
                        'new_data'  => $item->getAttributes(),
                    ]
                );
            } else {
                $this->userActivityLogService->log(
                    UserActivityLog::Category_ProductBrand,
                    UserActivityLog::Name_ProductBrand_Update,
                    "Merk Produk #$item->id:$item->name telah diperbarui.",
                    [
                        'formatter' => 'product-brand',
                        'new_data'  => $item->getAttributes(),
                        'old_data'  => $oldData,
                    ]
                );
            }

            return $item;
        });
    }

    /**
     * Menghapus Merk Produk dan mencatat aktivitas.
     *
     * @param ProductBrand $item
     * @return mixed
     * @throws \Exception Jika terjadi kesalahan saat transaksi DB.
     */
    public function delete(ProductBrand $item)
    {
        return DB::transaction(function () use ($item) {
            $deleted = $item->delete();

            $this->userActivityLogService->log(
                UserActivityLog::Category_ProductBrand,
                UserActivityLog::Name_ProductBrand_Delete,
                "Merk Produk #$item->id:$item->name telah dihapus.",
                [
                    'formatter' => 'product-brand',
                    'new_data'  => $item->toArray(),
                ]
            );

            return $deleted;
        });
    }
}
