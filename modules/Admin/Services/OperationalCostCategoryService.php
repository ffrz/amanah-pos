<?php

namespace Modules\Admin\Services;

use App\Models\OperationalCostCategory;
use App\Models\UserActivityLog;

use Illuminate\Support\Facades\DB;
use Modules\Admin\Services\UserActivityLogService;

class OperationalCostCategoryService
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

        $query = OperationalCostCategory::query();

        if (!empty($filter['search'])) {
            $query->where('name', 'like', '%' . $filter['search'] . '%');
        }

        $query->orderBy($options['order_by'], $options['order_type']);

        return $query->paginate($options['per_page'] ?? 10);
    }

    /**
     * Mencari kategori berdasarkan ID.
     *
     * @param int $id
     * @return OperationalCostCategory|null
     */
    public function find(int $id): ?OperationalCostCategory
    {
        return OperationalCostCategory::findOrFail($id);
    }

    /**
     * Membuat model duplikat (menggunakan instance yang sudah ada di service).
     *
     * @param int $id
     * @return OperationalCostCategory
     */
    public function duplicate(int $id): OperationalCostCategory
    {
        $item = $this->find($id);
        $duplicate = $item->replicate();
        $duplicate->name = $item->name;
        return $duplicate;
    }

    /**
     * Menyimpan kategori biaya operasional baru atau yang sudah ada (termasuk fill, transaksi, dan logging).
     *
     * @param OperationalCostCategory $item Model yang akan disimpan.
     * @param array $validatedData Data yang divalidasi dari request.
     * @return OperationalCostCategory Model yang telah disimpan.
     * @throws \Exception Jika terjadi kesalahan saat transaksi DB.
     */
    public function save(OperationalCostCategory $item, array $validatedData): OperationalCostCategory
    {
        $isCreating = $item->exists === false;
        $oldData = $item->toArray();

        $item->fill($validatedData);

        if (empty($item->getDirty())) {
            return $item;
        }

        DB::beginTransaction();
        try {
            $item->save();

            if ($isCreating) {
                $this->userActivityLogService->log(
                    UserActivityLog::Category_OperationalCost,
                    UserActivityLog::Name_OperationalCostCategory_Create,
                    "Kategori biaya ID: $item->id telah dibuat.",
                    [
                        'formatter' => 'operational-cost-category',
                        'new_data'  => $item->getAttributes(),
                    ]
                );
            } else {
                $this->userActivityLogService->log(
                    UserActivityLog::Category_OperationalCost,
                    UserActivityLog::Name_OperationalCostCategory_Update,
                    "Kategori biaya ID: $item->id telah diperbarui.",
                    [
                        'formatter' => 'operational-cost-category',
                        'new_data'  => $item->getAttributes(),
                        'old_data'  => $oldData,
                    ]
                );
            }
            DB::commit();
            return $item;
        } catch (\Throwable $ex) {
            DB::rollBack();
            throw new \Exception("Gagal saat menyimpan kategori biaya operasional ID: " . ($item->id ?? 'baru') . ". " . $ex->getMessage());
        }
    }

    /**
     * Menghapus kategori biaya operasional dan mencatat aktivitas.
     *
     * @param OperationalCostCategory $item
     * @return void
     * @throws \Exception Jika terjadi kesalahan saat transaksi DB.
     */
    public function delete(OperationalCostCategory $item): void
    {
        DB::beginTransaction();
        try {
            $deletedData = $item->toArray();

            $item->delete();

            $this->userActivityLogService->log(
                UserActivityLog::Category_OperationalCost,
                UserActivityLog::Name_OperationalCostCategory_Delete,
                "Kategori biaya ID: $item->id telah dihapus.",
                [
                    'formatter' => 'operational-cost-category',
                    'new_data'  => $deletedData,
                ]
            );

            DB::commit();
        } catch (\Throwable $ex) {
            DB::rollBack();
            throw new \Exception("Gagal menghapus kategori biaya operasional ID: $item->id. " . $ex->getMessage());
        }
    }
}
