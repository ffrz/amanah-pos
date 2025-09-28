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

namespace App\Services;

use App\Models\OperationalCostCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * File ini berisi semua logika bisnis untuk manajemen Kategori Biaya Operasional.
 * Kontroler akan memanggil metode dari kelas ini.
 */
class OperationalCostCategoryService
{
    /**
     * Mengambil daftar kategori biaya operasional dengan paginasi dan filter.
     *
     * @param array $params
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getData($data)
    {
        extract($data);

        $query = OperationalCostCategory::query();

        if (!empty($filter['search'])) {
            $query->where(function (Builder $q) use ($filter) {
                $q->where('name', 'like', '%' . $filter['search'] . '%')
                    ->orWhere('description', 'like', '%' . $filter['search'] . '%');
            });
        }

        return $query->orderBy($order_by, $order_type)->paginate($per_page);
    }

    /**
     * Mencari kategori biaya operasional berdasarkan ID.
     *
     * @param int|null $id
     * @return OperationalCostCategory
     * @throws ModelNotFoundException
     */
    public function find(?int $id): OperationalCostCategory
    {
        if (!$id) {
            return new OperationalCostCategory();
        }
        return OperationalCostCategory::findOrFail($id);
    }

    /**
     * Menyimpan (membuat atau memperbarui) kategori biaya operasional.
     *
     * @param array $data Data yang divalidasi dari FormRequest.
     * @param int|null $id ID kategori yang akan diperbarui (null jika membuat baru).
     * @return OperationalCostCategory
     */
    public function save(OperationalCostCategory $item, array $data)
    {
        $item->fill($data);
        return $item->save();
    }

    /**
     * Menghapus kategori biaya operasional.
     *
     * @param int $id
     * @return void
     */
    public function delete(OperationalCostCategory $item)
    {
        return $item->delete();
    }
}
