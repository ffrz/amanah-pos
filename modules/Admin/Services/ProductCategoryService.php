<?php

/**
 * Proprietary Software / Perangkat Lunak Proprietary
 * Copyright (c) 2025 Fahmi Fauzi Rahman. All rights reserved.
 * * EN: Unauthorized use, copying, modification, or distribution is prohibited.
 * ID: Penggunaan, penyalinan, modifikasi, atau distribusi tanpa izin dilarang.
 * * See the LICENSE file in the project root for full license information.
 * Lihat file LICENSE di root proyek untuk informasi lisensi lengkap.
 * * GitHub: https://github.com/ffrz
 * Email: fahmifauzirahman@gmail.com
 */

namespace Modules\Admin\Services;

use App\Exceptions\ModelNotModifiedException;
use App\Models\ProductCategory;
use App\Models\UserActivityLog;
use App\Repositories\Contracts\ProductCategoryRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Class ProductCategoryService
 * Kelas ini berisi semua logika bisnis inti, query, dan manajemen transaksi
 * (CRUD, logging) untuk entitas Kategori Produk.
 * Ini adalah lapisan "Fat Service" yang dipanggil oleh Controller.
 */
class ProductCategoryService
{
    // // Konstanta untuk Kunci Cache (agar tidak bentrok dengan Service lain)
    // public const CACHE_KEY_PRODUCT_CATEGORIES = 'common_data:product_categories';
    // public const CACHE_TTL_MINUTES = 60; // Cache selama 60 menit (1 jam)

    /**
     * @param UserActivityLogService $userActivityLogService Service untuk mencatat aktivitas pengguna.
     * @param ProductCategoryRepositoryInterface $categoryRepository Repository untuk akses data Kategori Produk.
     */
    public function __construct(
        protected UserActivityLogService $userActivityLogService,
        protected ProductCategoryRepositoryInterface $categoryRepository
    ) {}

    /**
     * Mengambil data kategori produk dengan pemfilteran dan paginasi.
     *
     * @param array $options Array berisi kriteria filter, seperti ['search' => 'kata_kunci'].
     * @return LengthAwarePaginator
     */
    public function getData($options): LengthAwarePaginator
    {
        return $this->categoryRepository->getData($options);
    }

    /**
     * Mencari kategori produk berdasarkan ID.
     *
     * @param int $id ID Kategori Produk.
     * @return ProductCategory
     * @throws ModelNotFoundException
     */
    public function find(int $id): ProductCategory
    {
        return $this->categoryRepository->find($id);
    }

    /**
     * Mencari kategori produk berdasarkan ID.
     *
     * @param int $id ID Kategori Produk.
     * @return ProductCategory
     * @throws ModelNotFoundException
     */
    public function findOrCreate($id): ProductCategory
    {
        return $this->categoryRepository->findOrCreate($id);
    }

    /**
     * Menduplikasi kategori produk yang sudah ada.
     *
     * @param int $id ID kategori yang akan diduplikasi.
     * @return ProductCategory Model ProductCategory baru yang sudah diinisialisasi untuk pembuatan.
     * @throws ModelNotFoundException
     */
    public function duplicate(int $id): ProductCategory
    {
        return $this->find($id)->replicate();
    }

    /**
     * Menyimpan (membuat atau memperbarui) kategori produk.
     *
     * @param ProductCategory $item Model ProductCategory yang sudah diinisialisasi atau dicari.
     * @param array $data Data tervalidasi dari SaveRequest untuk mengisi model.
     * @return mixed
     * @throws Throwable Jika transaksi database gagal.
     */
    public function save(ProductCategory $item, array $data): mixed
    {
        $oldData = $item->getAttributes();
        $isNew   = !$item->id;

        $item->fill($data);

        if (empty($item->getDirty())) {
            throw new ModelNotModifiedException();
        }

        return DB::transaction(function () use ($item, $oldData, $isNew) {
            $item = $this->categoryRepository->save($item);
            // Cache::forget(self::CACHE_KEY_PRODUCT_CATEGORIES);

            if ($isNew) {
                $this->userActivityLogService->log(
                    UserActivityLog::Category_ProductCategory,
                    UserActivityLog::Name_ProductCategory_Create,
                    "Kategori $item->name telah ditambahkan.",
                    [
                        'formatter' => 'product-category',
                        'data' => $item->getAttributes(),
                    ]
                );
            } else {
                $this->userActivityLogService->log(
                    UserActivityLog::Category_ProductCategory,
                    UserActivityLog::Name_ProductCategory_Update,
                    "Kategori $item->name telah diperbarui.",
                    [
                        'formatter' => 'product-category',
                        'old_data' => $oldData,
                        'new_data' => $item->getAttributes(),
                    ]
                );
            }

            return $item;
        });
    }

    /**
     * Menghapus kategori produk.
     *
     * @param ProductCategory $item Model ProductCategory yang akan dihapus.
     * @return mixed 
     * @throws Throwable Jika transaksi database gagal.
     */
    public function delete(ProductCategory $item)
    {
        return DB::transaction(function () use ($item) {
            $this->categoryRepository->delete($item);
            // Cache::forget(self::CACHE_KEY_PRODUCT_CATEGORIES);

            $this->userActivityLogService->log(
                UserActivityLog::Category_ProductCategory,
                UserActivityLog::Name_ProductCategory_Delete,
                "Kategori $item->name telah dihapus.",
                [
                    'formatter' => 'product-category',
                    'data' => $item->getAttributes(),
                ]
            );

            return $item;
        });
    }

    public function getAllProductCategories()
    {
        return $this->categoryRepository->getAll(
            ['id', 'name'],
            ['order_by' => 'name', 'order_type' => 'asc'],
        );
    }
}
