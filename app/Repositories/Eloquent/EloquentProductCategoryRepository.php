<?php

namespace App\Repositories\Eloquent;

use App\Models\ProductCategory;
use App\Repositories\Contracts\ProductCategoryRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class EloquentProductCategoryRepository
 * Implementasi Repository untuk ProductCategory menggunakan Eloquent ORM.
 */
class EloquentProductCategoryRepository implements ProductCategoryRepositoryInterface
{
    /**
     * Injeksi Model Eloquent.
     */
    public function __construct(protected ProductCategory $model) {}

    /**
     * Mengambil data kategori produk dengan pemfilteran dan paginasi.
     */
    public function getData(array $options): LengthAwarePaginator
    {
        $q = $this->model->query();

        $filter = $options['filter'];

        if (!empty($filter['search'])) {
            $q->where(function (Builder $q) use ($filter) {
                $q->where('name', 'like', '%' . $filter['search'] . '%')
                    ->orWhere('description', 'like', '%' . $filter['search'] . '%');
            });
        }

        $q->orderBy($options['order_by'], $options['order_type']);

        return $q->paginate($options['per_page'])->withQueryString();
    }

    /**
     * Mencari kategori produk berdasarkan ID, gagal jika tidak ditemukan.
     */
    public function find(int $id): ProductCategory
    {
        // Pindahkan logika Eloquent findOrFail()
        return $this->model->findOrFail($id);
    }

    /**
     * Menginisialisasi model baru atau mencarinya berdasarkan ID.
     */
    public function findOrCreate(?int $id): ProductCategory
    {
        // Pindahkan logika
        return $id ? $this->find($id) : new ProductCategory();
    }

    /**
     * Menyimpan (membuat atau memperbarui) model kategori.
     * Repository hanya bertanggung jawab untuk Persistence.
     */
    public function save(ProductCategory $item): ProductCategory
    {
        $item->save();
        return $item;
    }

    /**
     * Menghapus model kategori.
     * Repository hanya bertanggung jawab untuk Persistence.
     */
    public function delete(ProductCategory $item): ?bool
    {
        return $item->delete();
    }

    public function getAll(
        $fields,
        $options
    ) {
        $query = ProductCategory::query()
            ->orderBy($options['order_by'] ?? 'name', $options['order_type'] ?? 'asc');
        return $query->get($fields);
    }
}
