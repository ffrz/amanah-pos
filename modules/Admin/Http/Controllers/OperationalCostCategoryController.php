<?php

namespace Modules\Admin\Http\Controllers;

use App\Helpers\JsonResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\OperationalCostCategory\GetOperationalCostCategoriesRequest;
use App\Http\Requests\OperationalCostCategory\SaveOperationalCostCategoryRequest;
use App\Models\OperationalCostCategory;
use App\Models\UserActivityLog;
use App\Services\OperationalCostCategoryService;
use App\Services\UserActivityLogService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OperationalCostCategoryController extends Controller
{
    /**
     * Buat instance kontroler baru.
     *
     * @param OperationalCostCategoryService $operationalCostCategoryService
     * @param UserActivityLogService $userActivityLogService
     * @return void
     */
    public function __construct(
        protected OperationalCostCategoryService $operationalCostCategoryService,
        protected UserActivityLogService $userActivityLogService
    ) {}

    /**
     * Menampilkan halaman indeks kategori biaya operasional.
     *
     * @return Response
     */
    public function index(): Response
    {
        $this->authorize('viewAny', OperationalCostCategory::class);

        return Inertia::render('operational-cost-category/Index');
    }

    /**
     * Mengambil data kategori biaya operasional dengan paginasi dan filter.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function data(GetOperationalCostCategoriesRequest $request): JsonResponse
    {
        $this->authorize('viewAny', OperationalCostCategory::class);

        $items = $this->operationalCostCategoryService->getData($request->validated());

        return JsonResponseHelper::success($items);
    }

    /**
     * Menampilkan formulir untuk menduplikasi kategori.
     *
     * @param int $id
     * @return Response
     */
    public function duplicate(int $id): Response
    {
        $this->authorize('create', OperationalCostCategory::class);

        $item = $this->operationalCostCategoryService->duplicate($id);

        return Inertia::render('operational-cost-category/Editor', [
            'data' => $item
        ]);
    }

    /**
     * Menampilkan formulir editor untuk membuat atau mengedit kategori.
     *
     * @param int $id
     * @return Response
     */
    public function editor(int $id = 0): Response
    {
        $item = $id ? $this->operationalCostCategoryService->find($id) : new OperationalCostCategory();

        $this->authorize($id ? 'update' : 'create', $item);

        return Inertia::render('operational-cost-category/Editor', [
            'data' => $item,
        ]);
    }

    /**
     * Menyimpan kategori biaya operasional baru atau yang sudah ada.
     *
     * @param SaveOperationalCostCategoryRequest $request
     * @return RedirectResponse
     */
    public function save(SaveOperationalCostCategoryRequest $request): RedirectResponse
    {
        $item = $request->id ? $this->operationalCostCategoryService->find($request->id) : new OperationalCostCategory();

        $this->authorize($request->id ? 'update' : 'create', $item);

        $oldData = $item->toArray();

        $item->fill($request->validated());

        if (empty($item->getDirty())) {
            return redirect(route('admin.operational-cost-category.index'))
                ->with('success', "Tidak ada perubahan data.");
        }

        try {
            DB::beginTransaction();

            $this->operationalCostCategoryService->save($item);

            if (!$request->id) {
                $this->userActivityLogService->log(
                    UserActivityLog::Category_OperationalCost,
                    UserActivityLog::Name_OperationalCostCategory_Create,
                    "Kategori biaya ID: $item->id telah disimpan.",
                    $item->toArray()
                );
            } else {
                $this->userActivityLogService->log(
                    UserActivityLog::Category_OperationalCost,
                    UserActivityLog::Name_OperationalCostCategory_Update,
                    "Kategori biaya ID: $item->id telah diperbarui.",
                    [
                        'new_data' => $item->toArray(),
                        'old_data' => $oldData,
                    ]
                );
            }

            DB::commit();

            return redirect(route('admin.operational-cost-category.index'))
                ->with('success', "Kategori $item->name telah disimpan.");
        } catch (\Throwable $ex) {
            DB::rollBack();

            Log::error("Gagal menghapus kategori biaya operasional ID: $item->id", ['exception' => $ex]);
        }

        return redirect(route('admin.operational-cost-category.index'))
            ->with('error', "Kategori $item->name gagal disimpan.");
    }

    /**
     * Menghapus kategori biaya operasional.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function delete(int $id): JsonResponse
    {
        $item = $this->operationalCostCategoryService->find($id);

        $this->authorize('delete', $item);

        try {
            DB::beginTransaction();

            $this->operationalCostCategoryService->delete($item);

            $this->userActivityLogService->log(
                UserActivityLog::Category_OperationalCost,
                UserActivityLog::Name_OperationalCostCategory_Delete,
                "Kategori biaya ID: $item->id telah dihapus.",
                $item->toArray()
            );

            DB::commit();

            return JsonResponseHelper::success($item, "Kategori $item?->name telah dihapus");
        } catch (\Throwable $ex) {
            DB::rollBack();
            Log::error("Gagal menghapus kategori biaya operasional ID: $id", ['exception' => $ex]);
        }

        return JsonResponseHelper::error("Gagal saat menghapus kategori biaya operasional.");
    }
}
