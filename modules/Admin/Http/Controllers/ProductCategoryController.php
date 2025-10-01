<?php

namespace Modules\Admin\Http\Controllers;

use App\Helpers\JsonResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Modules\Admin\Services\ProductCategoryService;
use Modules\Admin\Http\Requests\ProductCategory\GetDataRequest;
use Modules\Admin\Http\Requests\ProductCategory\SaveRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Throwable; // Import Throwable untuk penanganan error yang lebih baik

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
     * * @param ProductCategoryService $productCategoryService
     */
    public function __construct(
        protected ProductCategoryService $productCategoryService,
    ) {}

    /**
     * Menampilkan halaman indeks kategori produk.
     * * Menerapkan otorisasi 'viewAny'.
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
     * * Semua logika query didelegasikan ke Service, hanya menerima data yang sudah divalidasi.
     *
     * @param GetDataRequest $request Request yang sudah tervalidasi dan ternormalisasi.
     * @return JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function data(GetDataRequest $request): JsonResponse
    {
        $this->authorize('viewAny', ProductCategory::class);
        $validated = $request->validated();
        $items = $this->productCategoryService->getData(
            $validated['filter'],
            $validated['order_by'],
            $validated['order_type'],
            $validated['per_page']
        );
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
     * @return Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException Jika ID ditemukan tetapi tidak ada.
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
     * Menyimpan (membuat atau memperbarui) kategori produk.
     * * Controller menangani otorisasi dan pengecekan 'no change',
     * lalu mendelegasikan transaksi dan logging ke Service.
     *
     * @param SaveRequest $request Request yang berisi data yang divalidasi.
     * @return RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function save(SaveRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Persiapan model dan otorisasi
        $item = $request->id ? $this->productCategoryService->find($request->id) : new ProductCategory();
        $this->authorize($request->id ? 'update' : 'create', $item);

        // Pengecekan 'no change' (logika Controller)
        $tempItem = $item->replicate()->fill($validated);
        if ($item->exists && empty($tempItem->getDirty())) {
            return redirect(route('admin.product-category.index'))
                ->with('success', "Tidak terdeteksi perubahan pada rekaman.");
        }

        try {
            // Mendelegasikan SELURUH proses (DB, Transaksi, Logging) ke Service
            $item = $this->productCategoryService->save($item, $validated);

            return redirect(route('admin.product-category.index'))
                ->with('success', "Kategori $item->name telah disimpan.");
        } catch (Throwable $ex) { // Catch Throwable untuk semua jenis error (termasuk error DB)
            Log::error("Gagal menyimpan kategori produk", ['exception' => $ex]);
        }

        return redirect()->back()->withInput()
            ->with('error', "Gagal menyimpan kategori produk $item->name.");
    }

    /**
     * Menghapus kategori produk berdasarkan ID.
     * * Controller menangani otorisasi dan penanganan error.
     * Logika penghapusan dan transaksional didelegasikan ke Service.
     *
     * @param int $id ID Kategori Produk yang akan dihapus.
     * @return JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException Jika ID tidak ditemukan.
     */
    public function delete(int $id): JsonResponse
    {
        try {
            $item = $this->productCategoryService->find($id);
            $this->authorize('delete', $item);

            // Mendelegasikan SELURUH proses (DB, Transaksi, Logging) ke Service
            $this->productCategoryService->delete($item);

            return JsonResponseHelper::success($item, "Kategori $item->name telah dihapus");
        } catch (Throwable $ex) {
            Log::error("Gagal menghapus kategori produk ID: $id", ['exception' => $ex]);
        }

        return JsonResponseHelper::error("Gagal menghapus kategori produk ID: $id");
    }
}
