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
use App\Http\Requests\OperationalCostCategory\GetOperationalCostCategoriesRequest;
use App\Http\Requests\OperationalCostCategory\SaveOperationalCostCategoryRequest;
use App\Models\OperationalCostCategory;
use App\Services\OperationalCostCategoryService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

class OperationalCostCategoryController extends Controller
{
    /**
     * Service yang berisi logika bisnis.
     *
     * @var OperationalCostCategoryService
     */
    protected OperationalCostCategoryService $service;

    /**
     * Buat instance kontroler baru.
     *
     * @param OperationalCostCategoryService $service
     * @return void
     */
    public function __construct(OperationalCostCategoryService $service)
    {
        $this->service = $service;
    }

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
        $items = $this->service->getData($request->validated());
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

        $item = $this->service->find($id);
        $item->id = null;

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
        if (!$id) {
            $this->authorize('create', OperationalCostCategory::class);
            $item = new OperationalCostCategory();
        } else {
            $item = $this->service->find($id);
            $this->authorize('update', $item);
        }
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
        $item = $this->service->find($request->id);

        $this->service->save($item, $request->validated());

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
        $item = $this->service->find($id);

        $this->authorize('delete', $item);

        try {
            $this->service->delete($item);
            return JsonResponseHelper::success($item, "Kategori $item?->name telah dihapus");
        } catch (\Throwable $ex) {
            return JsonResponseHelper::error("Gagal saat menghapus kategori.", 500, $ex);
        }
    }
}
