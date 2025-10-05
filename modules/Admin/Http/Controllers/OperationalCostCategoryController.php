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

namespace Modules\Admin\Http\Controllers;

use App\Helpers\JsonResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\OperationalCostCategory;

use Modules\Admin\Services\OperationalCostCategoryService;
use Modules\Admin\Http\Requests\OperationalCostCategory\GetDataRequest;
use Modules\Admin\Http\Requests\OperationalCostCategory\SaveRequest;

use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

class OperationalCostCategoryController extends Controller
{
    /**
     * Buat instance kontroler baru.
     *
     * @param OperationalCostCategoryService $operationalCostCategoryService

     * @return void
     */
    public function __construct(
        protected OperationalCostCategoryService $operationalCostCategoryService,
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
     * @param GetDataRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function data(GetDataRequest $request): JsonResponse
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
        $item = $this->operationalCostCategoryService->findOrCreate($id);

        $this->authorize($id ? 'update' : 'create', $item);

        return Inertia::render('operational-cost-category/Editor', [
            'data' => $item,
        ]);
    }

    /**
     * Menyimpan kategori biaya operasional baru atau yang sudah ada.
     *
     * @param SaveRequest $request
     * @return RedirectResponse
     */
    public function save(SaveRequest $request): RedirectResponse
    {
        $item = $this->operationalCostCategoryService->findOrCreate($request->id);

        $this->authorize($request->id ? 'update' : 'create', $item);

        $item = $this->operationalCostCategoryService->save($item, $request->validated());

        return redirect(route('admin.operational-cost-category.index'))
            ->with('success', "Kategori $item->name telah disimpan.");
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

        $this->operationalCostCategoryService->delete($item);

        return JsonResponseHelper::success(null, "Kategori $item->name telah dihapus");
    }
}
