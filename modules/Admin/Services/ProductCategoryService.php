<?php

namespace Modules\Admin\Services;

use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * File ini berisi semua logika bisnis untuk manajemen Kategori Produk.
 * Kontroler akan memanggil metode dari kelas ini.
 */
class ProductCategoryService
{
    /**
     * Mengambil daftar kategori produk dengan paginasi dan filter.
     *
     * @param array $data
     * @return LengthAwarePaginator
     */
    public function getData(array $data): LengthAwarePaginator
    {
        // Mendefinisikan variabel dari $data untuk kejelasan
        $filter = $data['filter'] ?? [];
        $per_page = $data['per_page'] ?? 10;
        $order_by = $data['order_by'] ?? 'id';
        $order_type = $data['order_type'] ?? 'asc';

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
     * @param int $id
     * @return ProductCategory
     * @throws ModelNotFoundException
     */
    public function find(int $id): ProductCategory
    {
        return ProductCategory::findOrFail($id);
    }

    /**
     * Menduplikasi kategori produk yang sudah ada.
     *
     * @param int $id ID kategori yang akan diduplikasi.
     * @return ProductCategory
     */
    public function duplicate(int $id): ProductCategory
    {
        $original = $this->find($id); // Mencari model asli

        // Kloning model, atur ID menjadi null, dan modifikasi nama
        $duplicate = $original->replicate();
        $duplicate->exists = false;
        $duplicate->id = null;
        $duplicate->name = $original->name . ' (COPY)';
        $duplicate->created_at = null;
        $duplicate->created_by = null;
        $duplicate->updated_at = null;
        $duplicate->updated_by = null;
        return $duplicate;
    }

    /**
     * Menyimpan (membuat atau memperbarui) kategori produk.
     *
     * @param ProductCategory $item Model yang sudah diisi datanya.
     * @return bool
     */
    public function save(ProductCategory $item): bool
    {
        return $item->save();
    }

    /**
     * Menghapus kategori produk.
     *
     * @param ProductCategory $item
     * @return bool|null
     */
    public function delete(ProductCategory $item): bool|null
    {
        return $item->delete();
    }
}
