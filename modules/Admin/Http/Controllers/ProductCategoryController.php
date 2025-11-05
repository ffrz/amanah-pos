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
use App\Models\ProductCategory;
use Modules\Admin\Services\ProductCategoryService;
use Modules\Admin\Http\Requests\ProductCategory\GetDataRequest;
use Modules\Admin\Http\Requests\ProductCategory\SaveRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Class ProductCategoryController
 * * Mengatur semua operasi HTTP (CRUD) untuk Kategori Produk.
 * Controller ini bertindak sebagai orkestrator yang mendelegasikan semua logika bisnis
 * dan transaksional ke ProductCategoryService.
 */
class ProductCategoryController extends Controller
{
    /**
     * Buat instance kontroler baru.
     * @param ProductCategoryService $productCategoryService
     */
    public function __construct(
        protected ProductCategoryService $productCategoryService,
    ) {}

    /**
     * Menampilkan halaman indeks kategori produk.
     *
     * @return Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(): Response
    {
        $this->authorize('viewAny', ProductCategory::class);

        return Inertia::render('product-category/Index');
    }

    /**
     * Mengambil data kategori produk dengan paginasi dan filter.
     *
     * @param GetDataRequest $request Request yang sudah tervalidasi dan ternormalisasi.
     * @return JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function data(GetDataRequest $request): JsonResponse
    {
        $this->authorize('viewAny', ProductCategory::class);

        $items = $this->productCategoryService->getData($request->validated());

        return JsonResponseHelper::success($items);
    }

    /**
     * Menampilkan formulir untuk menduplikasi kategori berdasarkan ID yang ada.
     *
     * @param int $id ID Kategori Produk yang akan diduplikasi.
     * @return Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException Jika ID tidak ditemukan.
     */
    public function duplicate(int $id): Response
    {
        $this->authorize('create', ProductCategory::class);

        $item = $this->productCategoryService->duplicate($id);

        return Inertia::render('product-category/Editor', [
            'data' => $item
        ]);
    }

    /**
     * Menampilkan formulir editor untuk membuat (id=0) atau mengedit kategori.
     *
     * @param int $id ID Kategori Produk (0 untuk membuat baru).
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException Jika ID ditemukan tetapi tidak ada.
     */
    public function editor(Request $request, int $id = 0)
    {
        $item = $this->productCategoryService->findOrCreate($id);

        $this->authorize($id ? 'update' : 'create', $item);

        if ($request->expectsJson()) {
            return JsonResponseHelper::success($item);
        }

        return Inertia::render('product-category/Editor', [
            'data' => $item,
        ]);
    }

    /**
     * Menyimpan (membuat atau memperbarui) kategori produk.
     *
     * @param SaveRequest $request Request yang berisi data yang divalidasi.
     * @return RedirectResponse|JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function save(SaveRequest $request): RedirectResponse|JsonResponse
    {
        $item = $this->productCategoryService->findOrCreate($request->id);

        $this->authorize($request->id ? 'update' : 'create', $item);

        $item = $this->productCategoryService->save($item, $request->validated());

        if ($request->expectsJson()) {
            return JsonResponseHelper::success($item);
        }

        return redirect(route('admin.product-category.index'))
            ->with('success', "Kategori $item->name telah disimpan.");
    }

    /**
     * Menghapus kategori produk berdasarkan ID.
     *
     * @param int $id ID Kategori Produk yang akan dihapus.
     * @return JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException Jika ID tidak ditemukan.
     */
    public function delete(int $id): JsonResponse
    {
        $item = $this->productCategoryService->find($id);

        $this->authorize('delete', $item);

        $this->productCategoryService->delete($item);

        return JsonResponseHelper::success($item, "Kategori $item->name telah dihapus");
    }
}
