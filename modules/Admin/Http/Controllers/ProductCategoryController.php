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
use App\Http\Requests\Product\GetDataRequest;
use App\Http\Requests\Product\SaveRequest;
use App\Http\Requests\ProductCategory\ProductCategoryGetDataRequest;
use App\Http\Requests\ProductCategorySaveRequest;
use App\Models\ProductCategory;
use App\Models\UserActivityLog;
use Modules\Admin\Services\ProductCategoryService;
use Modules\Admin\Services\UserActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class ProductCategoryController extends Controller
{
    /**
     * Buat instance kontroler baru menggunakan Constructor Property Promotion.
     *
     * @param ProductCategoryService $productCategoryService
     * @param UserActivityLogService $userActivityLogService
     * @return void
     */
    public function __construct(
        protected ProductCategoryService $productCategoryService,
        protected UserActivityLogService $userActivityLogService
    ) {}

    /**
     * Menampilkan halaman indeks kategori produk.
     *
     * @return Response
     */
    public function index(): Response
    {
        $this->authorize('viewAny', ProductCategory::class);

        return Inertia::render('product-category/Index');
    }

    /**
     * Mengambil data kategori produk dengan paginasi dan filter.
     *
     * @param GetDataRequest $request
     * @return JsonResponse
     */
    public function data(GetDataRequest $request): JsonResponse
    {
        $this->authorize('viewAny', ProductCategory::class);
        $items = $this->productCategoryService->getData($request->validated());

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
        $this->authorize('create', ProductCategory::class);

        $item = $this->productCategoryService->duplicate($id);

        return Inertia::render('product-category/Editor', [
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
        $item = $id ? $this->productCategoryService->find($id) : new ProductCategory();

        $this->authorize($id ? 'update' : 'create', $item);

        return Inertia::render('product-category/Editor', [
            'data' => $item,
        ]);
    }

    /**
     * Menyimpan kategori produk baru atau yang sudah ada.
     *
     * @param SaveRequest $request
     * @return RedirectResponse
     */
    public function save(SaveRequest $request): RedirectResponse
    {
        $item = $request->id ? $this->productCategoryService->find($request->id) : new ProductCategory();

        $this->authorize($request->id ? 'update' : 'create', $item);

        $oldData = $item->toArray();

        $item->fill($request->validated());

        if ($request->id && empty($item->getDirty())) {
            return redirect(route('admin.product-category.index'))
                ->with('success', "Tidak terdeteksi perubahan pada rekaman.");
        }

        try {
            DB::beginTransaction();

            $this->productCategoryService->save($item); // Panggil save tanpa $request->validated(), karena model sudah diisi.

            if (!$request->id) {
                $this->userActivityLogService->log(
                    UserActivityLog::Category_Product,
                    UserActivityLog::Name_ProductCategory_Create,
                    "Kategori $item->name telah ditambahkan.",
                    $item->toArray(),
                );
            } else {
                $this->userActivityLogService->log(
                    UserActivityLog::Category_Product,
                    UserActivityLog::Name_ProductCategory_Update,
                    "Kategori $item->name telah diperbarui.",
                    [
                        'new_data' => $item->getChanges(), // Hanya log perubahan, bukan seluruh data baru
                        'old_data' => $oldData,
                    ]
                );
            }

            DB::commit();

            return redirect(route('admin.product-category.index'))
                ->with('success', "Kategori $item->name telah disimpan.");
        } catch (\Throwable $ex) {
            DB::rollBack();

            Log::error("Gagal menyimpan kategori produk ID: $item->id", ['exception' => $ex]);
        }

        return redirect()->back()->withInput()
            ->with('error', "Gagal menyimpan kategori produk $item->name.");
    }

    /**
     * Menghapus kategori produk.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function delete(int $id): JsonResponse
    {
        $item = $this->productCategoryService->find($id);

        $this->authorize('delete', $item);

        try {
            DB::beginTransaction();

            $this->productCategoryService->delete($item);

            $this->userActivityLogService->log(
                UserActivityLog::Category_Product,
                UserActivityLog::Name_ProductCategory_Delete,
                "Kategori $item->name telah dihapus.",
                $item->toArray()
            );

            DB::commit();

            return JsonResponseHelper::success($item, "Kategori $item?->name telah dihapus");
        } catch (\Throwable $ex) {
            DB::rollBack();
            Log::error("Gagal menghapus kategori produk ID: $id", ['exception' => $ex]);
        }

        return JsonResponseHelper::error("Gagal menghapus kategori produk $item->name");
    }
}
