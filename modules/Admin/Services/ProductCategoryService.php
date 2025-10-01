<?php

namespace Modules\Admin\Services;

use App\Models\ProductCategory;
use App\Models\UserActivityLog;

use Modules\Admin\Http\Requests\ProductCategory\GetDataRequest;
use Modules\Admin\Http\Requests\ProductCategory\SaveRequest;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Throwable; // Tambahkan import Throwable untuk PHPDoc

/**
 * Class ProductCategoryService
 * Kelas ini berisi semua logika bisnis inti, query, dan manajemen transaksi
 * (CRUD, logging) untuk entitas Kategori Produk.
 * Ini adalah lapisan "Fat Service" yang dipanggil oleh Controller.
 */
class ProductCategoryService
{
    /**
     * @param UserActivityLogService $userActivityLogService Service untuk mencatat aktivitas pengguna.
     */
    public function __construct(
        protected UserActivityLogService $userActivityLogService
    ) {}

    /**
     * Mengambil data kategori produk dengan pemfilteran dan paginasi.
     *
     * @param array $filter Array berisi kriteria filter, seperti ['search' => 'kata_kunci'].
     * @param string $orderBy Kolom yang digunakan untuk mengurutkan data (e.g., 'name', 'created_at').
     * @param string $orderType Tipe urutan ('asc' atau 'desc').
     * @param int $perPage Jumlah item per halaman untuk paginasi.
     * @return LengthAwarePaginator
     */
    public function getData($filter, $orderBy, $orderType, $perPage): LengthAwarePaginator
    {
        $q = ProductCategory::query();

        if (!empty($filter['search'])) {
            $q->where(function (Builder $q) use ($filter) {
                $q->where('name', 'like', '%' . $filter['search'] . '%')
                    ->orWhere('description', 'like', '%' . $filter['search'] . '%');
            });
        }

        $q->orderBy($orderBy, $orderType);

        return $q->paginate($perPage)->withQueryString();
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
        return ProductCategory::findOrFail($id);
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
        $duplicate = $this->find($id)->replicate();
        $duplicate->exists = false;
        $duplicate->id = null;
        $duplicate->created_at = null;
        $duplicate->created_by = null;
        $duplicate->updated_at = null;
        $duplicate->updated_by = null;
        return $duplicate;
    }

    /**
     * Menyimpan (membuat atau memperbarui) kategori produk.
     *
     * @param ProductCategory $item Model ProductCategory yang sudah diinisialisasi atau dicari.
     * @param array $data Data tervalidasi dari SaveRequest untuk mengisi model.
     * @return ProductCategory Model yang telah disimpan (dengan ID jika baru).
     * @throws Throwable Jika transaksi database gagal.
     */
    public function save(ProductCategory $item, array $data): ProductCategory
    {
        $oldData = $item->getAttributes();
        $isCreating = !$item->exists;
        $item->fill($data);

        DB::transaction(function () use ($item, $oldData, $isCreating) {
            $item->save();

            // Logging Aktivitas
            if ($isCreating) {
                $this->userActivityLogService->log(
                    UserActivityLog::Category_Product,
                    UserActivityLog::Name_ProductCategory_Create,
                    "Kategori $item->name telah ditambahkan.",
                    $item->getAttributes(), // Log data lengkap yang baru
                );
            } else {
                $this->userActivityLogService->log(
                    UserActivityLog::Category_Product,
                    UserActivityLog::Name_ProductCategory_Update,
                    "Kategori $item->name telah diperbarui.",
                    [
                        'new_data' => $item->getChanges(), // Log hanya perubahan yang terjadi
                        'old_data' => $oldData,
                    ]
                );
            }
        });

        return $item;
    }

    /**
     * Menghapus kategori produk.
     *
     * @param ProductCategory $item Model ProductCategory yang akan dihapus.
     * @return ProductCategory Model yang telah dihapus (sebelum di-soft delete).
     * @throws Throwable Jika transaksi database gagal.
     */
    public function delete(ProductCategory $item): ProductCategory
    {
        $oldData = $item->toArray();
        $itemName = $item->name;

        // Transaksi sebagai Unit of Work
        DB::transaction(function () use ($item, $oldData, $itemName) {
            $item->delete();

            // Logging Aktivitas
            $this->userActivityLogService->log(
                UserActivityLog::Category_Product,
                UserActivityLog::Name_ProductCategory_Delete,
                "Kategori $itemName telah dihapus.",
                $oldData // Log data lengkap sebelum dihapus
            );
        });

        return $item;
    }
}
