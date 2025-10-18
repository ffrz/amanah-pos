<?php

namespace App\Repositories\Contracts;

use App\Models\ProductCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProductCategoryRepositoryInterface
{
    /**
     * Mengambil data kategori produk dengan pemfilteran dan paginasi.
     * @param array $options Opsi paginasi, filter, dan sorting.
     * @return LengthAwarePaginator
     */
    public function getData(array $options): LengthAwarePaginator;

    /**
     * Mencari kategori produk berdasarkan ID, gagal jika tidak ditemukan.
     * @param int $id ID Kategori Produk.
     * @return ProductCategory
     */
    public function find(int $id): ProductCategory;

    /**
     * Menginisialisasi model baru atau mencarinya berdasarkan ID.
     * @param int|null $id ID Kategori Produk atau null untuk model baru.
     * @return ProductCategory
     */
    public function findOrCreate(?int $id): ProductCategory;

    /**
     * Menyimpan (membuat atau memperbarui) model kategori.
     * @param ProductCategory $item Model ProductCategory.
     * @return ProductCategory
     */
    public function save(ProductCategory $item): ProductCategory;

    /**
     * Menghapus model kategori.
     * @param ProductCategory $item Model ProductCategory yang akan dihapus.
     * @return bool|null
     */
    public function delete(ProductCategory $item): ?bool;

    public function getAll(
        $fields,
        $order_options
    );
}
