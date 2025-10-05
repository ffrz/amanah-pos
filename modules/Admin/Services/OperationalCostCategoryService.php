<?php

namespace Modules\Admin\Services;

use App\Exceptions\ModelNotModifiedException;
use App\Models\OperationalCost;
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
            $query->orWhere('description', 'like', '%' . $filter['search'] . '%');
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
    public function find($id): ?OperationalCostCategory
    {
        return OperationalCostCategory::findOrFail($id);
    }

    public function findOrCreate($id): OperationalCostCategory
    {
        return $id ? $this->find($id) : new OperationalCostCategory();
    }

    /**
     * Membuat model duplikat (menggunakan instance yang sudah ada di service).
     *
     * @param int $id
     * @return OperationalCostCategory
     */
    public function duplicate($id): OperationalCostCategory
    {
        return $this->find($id)->replicate();
    }

    /**
     * Menyimpan kategori biaya operasional baru atau yang sudah ada (termasuk fill, transaksi, dan logging).
     *
     * @param OperationalCostCategory $item Model yang akan disimpan.
     * @param array $validatedData Data yang divalidasi dari request.
     * @return mixed
     * @throws \Exception Jika terjadi kesalahan saat transaksi DB.
     */
    public function save(OperationalCostCategory $item, array $data): OperationalCostCategory
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
                    UserActivityLog::Category_OperationalCostCategory,
                    UserActivityLog::Name_OperationalCostCategory_Create,
                    "Kategori biaya ID: $item->id telah dibuat.",
                    [
                        'formatter' => 'operational-cost-category',
                        'new_data'  => $item->getAttributes(),
                    ]
                );
            } else {
                $this->userActivityLogService->log(
                    UserActivityLog::Category_OperationalCostCategory,
                    UserActivityLog::Name_OperationalCostCategory_Update,
                    "Kategori biaya ID: $item->id telah diperbarui.",
                    [
                        'formatter' => 'operational-cost-category',
                        'new_data'  => $item->getAttributes(),
                        'old_data'  => $oldData,
                    ]
                );
            }

            return $item;
        });
    }

    /**
     * Menghapus kategori biaya operasional dan mencatat aktivitas.
     *
     * @param OperationalCostCategory $item
     * @return mixed
     * @throws \Exception Jika terjadi kesalahan saat transaksi DB.
     */
    public function delete(OperationalCostCategory $item)
    {
        return DB::transaction(function () use ($item) {
            $deleted = $item->delete();

            $this->userActivityLogService->log(
                UserActivityLog::Category_OperationalCostCategory,
                UserActivityLog::Name_OperationalCostCategory_Delete,
                "Kategori biaya ID: $item->id telah dihapus.",
                [
                    'formatter' => 'operational-cost-category',
                    'new_data'  => $item->toArray(),
                ]
            );

            return $deleted;
        });
    }
}
