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
use App\Models\FinanceTransactionTag;

use Modules\Admin\Services\FinanceTransactionTagService;
use Modules\Admin\Http\Requests\FinanceTransactionTag\GetDataRequest;
use Modules\Admin\Http\Requests\FinanceTransactionTag\SaveRequest;

use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

class FinanceTransactionTagController extends Controller
{
    /**
     * Buat instance kontroler baru.
     *
     * @param FinanceTransactionTagService $FinanceTransactionTagService

     * @return void
     */
    public function __construct(
        protected FinanceTransactionTagService $FinanceTransactionTagService,
    ) {}

    /**
     * Menampilkan halaman indeks kategori biaya operasional.
     *
     * @return Response
     */
    public function index(): Response
    {
        $this->authorize('viewAny', FinanceTransactionTag::class);

        return Inertia::render('finance-transaction-tag/Index');
    }

    /**
     * Mengambil data kategori biaya operasional dengan paginasi dan filter.
     *
     * @param GetDataRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function data(GetDataRequest $request): JsonResponse
    {
        $this->authorize('viewAny', FinanceTransactionTag::class);

        $items = $this->FinanceTransactionTagService->getData($request->validated());

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
        $this->authorize('create', FinanceTransactionTag::class);

        $item = $this->FinanceTransactionTagService->duplicate($id);

        return Inertia::render('finance-transaction-tag/Editor', [
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
        $item = $this->FinanceTransactionTagService->findOrCreate($id);

        $this->authorize($id ? 'update' : 'create', $item);

        return Inertia::render('finance-transaction-tag/Editor', [
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
        $item = $this->FinanceTransactionTagService->findOrCreate($request->id);

        $this->authorize($request->id ? 'update' : 'create', $item);

        $item = $this->FinanceTransactionTagService->save($item, $request->validated());

        return redirect(route('admin.finance-transaction-tag.index'))
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
        $item = $this->FinanceTransactionTagService->find($id);

        $this->authorize('delete', $item);

        $this->FinanceTransactionTagService->delete($item);

        return JsonResponseHelper::success(null, "Kategori $item->name telah dihapus");
    }
}
