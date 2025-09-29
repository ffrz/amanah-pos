<?php

namespace App\Services;

use App\Models\OperationalCostCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
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
     * @param array $data Parameter yang berisi filter, urutan, dan paginasi.
     * @return LengthAwarePaginator
     */
    public function getData(array $data): LengthAwarePaginator
    {
        // Hindari extract() untuk keamanan dan kejelasan scope variabel
        $filter = $data['filter'] ?? [];
        $orderBy = $data['order_by'] ?? 'id';
        $orderType = $data['order_type'] ?? 'asc';
        $perPage = $data['per_page'] ?? 10;

        $query = OperationalCostCategory::query();

        if (!empty($filter['search'])) {
            $query->where(function (Builder $q) use ($filter) {
                $searchTerm = '%' . $filter['search'] . '%';
                $q->where('name', 'like', $searchTerm)
                    ->orWhere('description', 'like', $searchTerm);
            });
        }

        return $query->orderBy($orderBy, $orderType)->paginate($perPage);
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
        return OperationalCostCategory::findOrFail($id);
    }

    /**
     * Menyimpan (membuat atau memperbarui) kategori biaya operasional.
     *
     * @param OperationalCostCategory $item Model yang sudah diisi datanya.
     * @return bool
     */
    public function save(OperationalCostCategory $item): bool
    {
        return $item->save();
    }

    /**
     * Menghapus kategori biaya operasional.
     *
     * @param OperationalCostCategory $item Model kategori yang akan dihapus.
     * @return bool
     */
    public function delete(OperationalCostCategory $item): bool
    {
        return $item->delete();
    }

    /**
     * Menduplikasi kategori biaya operasional yang sudah ada.
     *
     * @param int $id ID kategori yang akan diduplikasi.
     * @return OperationalCostCategory Model kategori baru yang sudah diduplikasi.
     * @throws ModelNotFoundException
     */
    public function duplicate(int $id): OperationalCostCategory
    {
        $original = OperationalCostCategory::findOrFail($id);
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
}
