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

use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * File ini berisi semua logika bisnis untuk manajemen Kategori Produk.
 * Kontroler akan memanggil metode dari kelas ini.
 */
class ProductCategoryService
{
    /**
     * Mengambil daftar kategori produk dengan paginasi dan filter.
     *
     * @param array $params
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getData($data)
    {
        extract($data);

        $query = ProductCategory::query();

        if (!empty($filter['search'])) {
            $query->where(function (Builder $q) use ($filter) {
                $q->where('name', 'like', '%' . $filter['search'] . '%')
                    ->orWhere('description', 'like', '%' . $filter['search'] . '%');
            });
        }

        return $query->orderBy($order_by, $order_type)->paginate($per_page);
    }

    /**
     * Mencari kategori produk berdasarkan ID.
     *
     * @param int|null $id
     * @return ProductCategory
     * @throws ModelNotFoundException
     */
    public function find(?int $id): ProductCategory
    {
        if (!$id) {
            return new ProductCategory();
        }
        return ProductCategory::findOrFail($id);
    }

    /**
     * Menyimpan (membuat atau memperbarui) kategori produk.
     *
     * @param array $data Data yang divalidasi dari FormRequest.
     * @param int|null $id ID kategori yang akan diperbarui (null jika membuat baru).
     * @return ProductCategory
     */
    public function save(ProductCategory $item, array $data)
    {
        $item->fill($data);
        return $item->save();
    }

    /**
     * Menghapus kategori produk.
     *
     * @param int $id
     * @return void
     */
    public function delete(ProductCategory $item)
    {
        return $item->delete();
    }
}
