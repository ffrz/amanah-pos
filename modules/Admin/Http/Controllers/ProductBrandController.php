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

use Modules\Admin\Services\ProductBrandService;
use Modules\Admin\Http\Requests\ProductBrand\GetDataRequest;
use Modules\Admin\Http\Requests\ProductBrand\SaveRequest;

use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductBrandController extends Controller
{
    /**
     * Buat instance kontroler baru.
     *
     * @param ProductBrandService $productBrandService

     * @return void
     */
    public function __construct(
        protected ProductBrandService $productBrandService,
    ) {}

    /**
     * Menampilkan halaman indeks kategori biaya operasional.
     *
     * @return Response
     */
    public function index(): Response
    {
        return Inertia::render('product-brand/Index');
    }

    /**
     * Mengambil data kategori biaya operasional dengan paginasi dan filter.
     *
     * @param GetDataRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function data(GetDataRequest $request): JsonResponse
    {
        $items = $this->productBrandService->getData($request->validated());
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
        $item = $this->productBrandService->duplicate($id);
        return Inertia::render('product-brand/Editor', [
            'data' => $item
        ]);
    }

    /**
     * Menampilkan formulir editor untuk membuat atau mengedit kategori.
     *
     * @param int $id
     * @return Response
     */
    public function editor(Request $request, int $id = 0)
    {
        $item = $this->productBrandService->findOrCreate($id);

        return Inertia::render('product-brand/Editor', [
            'data' => $item,
        ]);
    }

    /**
     * Menyimpan kategori biaya operasional baru atau yang sudah ada.
     *
     * @param SaveRequest $request
     * @return RedirectResponse
     */
    public function save(SaveRequest $request)
    {
        $item = $this->productBrandService->findOrCreate($request->id);

        $item = $this->productBrandService->save($item, $request->validated());

        if ($request->expectsJson()) {
            return JsonResponseHelper::success($item);
        }

        return redirect(route('admin.product-brand.index'))
            ->with('success', "Merk $item->name telah disimpan.");
    }

    /**
     * Menghapus kategori biaya operasional.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function delete(int $id): JsonResponse
    {
        $item = $this->productBrandService->find($id);

        $this->productBrandService->delete($item);

        return JsonResponseHelper::success(null, "Merk $item->name telah dihapus");
    }
}
