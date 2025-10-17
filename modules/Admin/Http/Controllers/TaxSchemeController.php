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
use App\Models\TaxScheme;
use Modules\Admin\Services\TaxSchemeService;
use Modules\Admin\Http\Requests\TaxScheme\GetDataRequest;
use Modules\Admin\Http\Requests\TaxScheme\SaveRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Class TaxSchemeController
 * * Mengatur semua operasi HTTP (CRUD) untuk Skema Pajak.
 * Controller ini bertindak sebagai orkestrator yang mendelegasikan semua logika bisnis
 * dan transaksional ke TaxSchemeService.
 */
class TaxSchemeController extends Controller
{
    /**
     * Buat instance kontroler baru.
     * @param Modules\Admin\Http\Controllers\TaxSchemeService $taxSchemeService
     */
    public function __construct(
        protected TaxSchemeService $taxSchemeService,
    ) {}

    /**
     * Menampilkan halaman indeks skema pajak.
     *
     * @return Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(): Response
    {
        $this->authorize('viewAny', TaxScheme::class);

        return Inertia::render('tax-scheme/Index');
    }

    /**
     * Mengambil data skema pajak dengan paginasi dan filter.
     *
     * @param GetDataRequest $request Request yang sudah tervalidasi dan ternormalisasi.
     * @return JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function data(GetDataRequest $request): JsonResponse
    {
        $this->authorize('viewAny', TaxScheme::class);

        $items = $this->taxSchemeService->getData($request->validated());

        return JsonResponseHelper::success($items);
    }

    /**
     * Menampilkan formulir untuk menduplikasi kategori berdasarkan ID yang ada.
     *
     * @param int $id ID Skema Pajak yang akan diduplikasi.
     * @return Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException Jika ID tidak ditemukan.
     */
    public function duplicate(int $id): Response
    {
        $this->authorize('create', TaxScheme::class);

        $item = $this->taxSchemeService->duplicate($id);

        return Inertia::render('tax-scheme/Editor', [
            'data' => $item
        ]);
    }

    /**
     * Menampilkan formulir editor untuk membuat (id=0) atau mengedit kategori.
     *
     * @param int $id ID Skema Pajak (0 untuk membuat baru).
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException Jika ID ditemukan tetapi tidak ada.
     */
    public function editor(int $id = 0)
    {
        $item = $this->taxSchemeService->findOrCreate($id);

        $this->authorize($id ? 'update' : 'create', $item);

        return Inertia::render('tax-scheme/Editor', [
            'data' => $item,
        ]);
    }

    /**
     * Menyimpan (membuat atau memperbarui) skema pajak.
     *
     * @param SaveRequest $request Request yang berisi data yang divalidasi.
     * @return RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function save(SaveRequest $request): RedirectResponse
    {
        $item = $this->taxSchemeService->findOrCreate($request->id);

        $this->authorize($request->id ? 'update' : 'create', $item);

        $item = $this->taxSchemeService->save($item, $request->validated());

        return redirect(route('admin.tax-scheme.index'))
            ->with('success', "Skema Pajak $item->name telah disimpan.");
    }

    /**
     * Menghapus skema pajak berdasarkan ID.
     *
     * @param int $id ID Skema Pajak yang akan dihapus.
     * @return JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException Jika ID tidak ditemukan.
     */
    public function delete(int $id): JsonResponse
    {
        $item = $this->taxSchemeService->find($id);

        $this->authorize('delete', $item);

        $this->taxSchemeService->delete($item);

        return JsonResponseHelper::success($item, "Skema Pajak $item->name telah dihapus");
    }
}
