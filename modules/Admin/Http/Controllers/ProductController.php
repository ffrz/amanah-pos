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

use App\Exceptions\ModelInUseException;
use App\Helpers\JsonResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Modules\Admin\Http\Requests\Product\GetDataRequest;
use Modules\Admin\Http\Requests\Product\SaveRequest;
use Modules\Admin\Services\CommonDataService;
use Modules\Admin\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Mengelola semua operasi yang berkaitan dengan Produk (CRUD, Data, Import).
 * Bertindak sebagai lapisan jembatan antara permintaan HTTP dan lapisan Service.
 */
class ProductController extends Controller
{
    public function __construct(
        protected ProductService $productService,
        protected CommonDataService $commonDataService,
    ) {}

    /**
     * Menampilkan halaman indeks daftar produk.
     * * @return Response
     */
    public function index(): Response
    {
        $this->authorize('viewAny', Product::class);
        return Inertia::render('product/Index', [
            'categories' => $this->commonDataService->getProductCategories(),
            'suppliers' => $this->commonDataService->getSuppliers(),
        ]);
    }

    /**
     * Menampilkan halaman detail produk.
     * * @param int $id ID Produk.
     * @return Response|RedirectResponse
     */
    public function detail(int $id = 0): Response|RedirectResponse
    {
        $item = $this->productService->find($id);
        $this->authorize('view', $item);
        return Inertia::render('product/Detail', [
            'data' => $item,
        ]);
    }

    /**
     * Mengambil data produk berpaginasi untuk tabel Inertia.
     *
     * @param GetDataRequest $request Permintaan yang divalidasi.
     */
    public function data(GetDataRequest $request)
    {
        $this->authorize('viewAny', Product::class);
        $items = $this->productService->getData($request->validated());
        return JsonResponseHelper::success($items);
    }

    /**
     * Memuat produk yang sudah ada ke dalam form editor untuk diduplikasi.
     *
     * @param int $id ID Produk yang akan diduplikasi.
     * @return Response
     */
    public function duplicate(int $id): Response
    {
        $this->authorize('create', Product::class);
        $item = $this->productService->duplicate($id);
        return Inertia::render('product/Editor', [
            'data' => $item,
            'categories' => $this->commonDataService->getProductCategories(),
            'suppliers' => $this->commonDataService->getSuppliers(),
        ]);
    }

    /**
     * Menampilkan form editor untuk membuat atau mengedit produk.
     *
     * @param int $id ID Produk, atau 0 untuk produk baru.
     * @return Response
     */
    public function editor(int $id = 0): Response
    {
        $item = $this->productService->findOrCreate($id);
        $this->authorize($id ? 'update' : 'create', $item);
        return Inertia::render('product/Editor', [
            'data' => $item,
            'categories' => $this->commonDataService->getProductCategories(),
            'suppliers' => $this->commonDataService->getSuppliers(),
        ]);
    }

    /**
     * Menyimpan (membuat atau memperbarui) produk.
     *
     * @param SaveRequest $request Permintaan yang divalidasi.
     * @return RedirectResponse
     */
    public function save(SaveRequest $request): RedirectResponse
    {
        $item = $this->productService->findOrCreate($request->id);
        $this->authorize($request->id ? 'update' : 'create', $item);
        $item = $this->productService->save($item, $request->validated());
        return redirect(route('admin.product.index'))
            ->with('success', "Produk $item->name telah disimpan.");
    }

    /**
     * Menghapus produk yang spesifik.
     * * @param int $id ID Produk.
     * @return \Illuminate\Http\JsonResponse Mengembalikan JSON Response karena ini endpoint yang sering dipanggil via AJAX.
     */
    public function delete(int $id): \Illuminate\Http\JsonResponse
    {
        $item = $this->productService->find($id);
        $this->authorize('delete', $item);
        $this->productService->delete($item);
        return JsonResponseHelper::success("Produk $item->name telah dihapus.");
    }

    /**
     * Menampilkan form import CSV (GET) atau memproses file CSV (POST).
     *
     * @param Request $request
     * @return Response|RedirectResponse
     */
    public function import(Request $request): Response|RedirectResponse
    {
        if ($request->getMethod() === Request::METHOD_POST) {
            $request->validate([
                'csv_file' => 'required|mimes:csv,txt|max:10240',
            ]);

            $file = $request->file('csv_file');

            if (!$this->productService->import($file)) {
                return redirect()->back()->with('error', 'Gagal mengimpor data.');
            }

            return redirect()->back()->with('success', 'Data produk berhasil diimpor!');
        }

        return inertia('product/Import');
    }
}
