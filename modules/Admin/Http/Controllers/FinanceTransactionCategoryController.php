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
use App\Models\FinanceTransactionCategory;

use Modules\Admin\Services\FinanceTransactionCategoryService;
use Modules\Admin\Http\Requests\FinanceTransactionCategory\GetDataRequest;
use Modules\Admin\Http\Requests\FinanceTransactionCategory\SaveRequest;

use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

class FinanceTransactionCategoryController extends Controller
{
    /**
     * Buat instance kontroler baru.
     *
     * @param FinanceTransactionCategoryService $FinanceTransactionCategoryService

     * @return void
     */
    public function __construct(
        protected FinanceTransactionCategoryService $financeTransactionCategoryService,
    ) {}

    /**
     * Menampilkan halaman indeks kategori biaya operasional.
     *
     * @return Response
     */
    public function index(): Response
    {
        $this->authorize('viewAny', FinanceTransactionCategory::class);

        return Inertia::render('finance-transaction-category/Index');
    }

    /**
     * Mengambil data kategori biaya operasional dengan paginasi dan filter.
     *
     * @param GetDataRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function data(GetDataRequest $request): JsonResponse
    {
        $this->authorize('viewAny', FinanceTransactionCategory::class);

        $items = $this->financeTransactionCategoryService->getData($request->validated());

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
        $this->authorize('create', FinanceTransactionCategory::class);

        $item = $this->financeTransactionCategoryService->duplicate($id);

        return Inertia::render('finance-transaction-category/Editor', [
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
        $item = $this->financeTransactionCategoryService->findOrCreate($id);

        $this->authorize($id ? 'update' : 'create', $item);

        return Inertia::render('finance-transaction-category/Editor', [
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
        $item = $this->financeTransactionCategoryService->findOrCreate($request->id);

        $this->authorize($request->id ? 'update' : 'create', $item);

        $item = $this->financeTransactionCategoryService->save($item, $request->validated());

        return redirect(route('admin.finance-transaction-category.index'))
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
        $item = $this->financeTransactionCategoryService->find($id);

        $this->authorize('delete', $item);

        $this->financeTransactionCategoryService->delete($item);

        return JsonResponseHelper::success(null, "Kategori $item->name telah dihapus");
    }
}
