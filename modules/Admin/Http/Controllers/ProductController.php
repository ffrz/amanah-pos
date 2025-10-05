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
        try {
            $item = $this->productService->find($id);

            return Inertia::render('product/Detail', [
                'data' => $item,
            ]);
        } catch (ModelNotFoundException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Mengambil data produk berpaginasi untuk tabel Inertia.
     *
     * @param GetDataRequest $request Permintaan yang divalidasi.
     * @return LengthAwarePaginator
     */
    public function data(GetDataRequest $request): LengthAwarePaginator
    {
        return $this->productService->getData($request->validated());
    }

    /**
     * Memuat produk yang sudah ada ke dalam form editor untuk diduplikasi.
     *
     * @param int $id ID Produk yang akan diduplikasi.
     * @return Response|RedirectResponse
     */
    public function duplicate(int $id): Response|RedirectResponse
    {
        try {
            $item = $this->productService->duplicate($id);

            return Inertia::render('product/Editor', [
                'data' => $item,
                'categories' => $this->commonDataService->getProductCategories(),
                'suppliers' => $this->commonDataService->getSuppliers(),
            ]);
        } catch (ModelNotFoundException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Menampilkan form editor untuk membuat atau mengedit produk.
     *
     * @param int $id ID Produk, atau 0 untuk produk baru.
     * @return Response|RedirectResponse
     */
    public function editor(int $id = 0): Response|RedirectResponse
    {
        try {
            $item = $this->productService->findOrCreate($id);

            return Inertia::render('product/Editor', [
                'data' => $item,
                'categories' => $this->commonDataService->getProductCategories(),
                'suppliers' => $this->commonDataService->getSuppliers(),
            ]);
        } catch (ModelNotFoundException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Menyimpan (membuat atau memperbarui) produk.
     *
     * @param SaveRequest $request Permintaan yang divalidasi.
     * @return RedirectResponse
     */
    public function save(SaveRequest $request): RedirectResponse
    {
        try {
            $item = $this->productService->save($request->validated());
            return redirect(route('admin.product.index'))
                ->with('success', "Produk $item->name telah disimpan.");
        } catch (\Exception $e) {
            Log::error("Gagal menyimpan produk $request->id", ['exception' => $e]);
        }

        return redirect()->back()->withInput()
            ->with('error', 'Gagal menyimpan produk.');
    }

    /**
     * Menghapus produk yang spesifik.
     * * @param int $id ID Produk.
     * @return \Illuminate\Http\JsonResponse Mengembalikan JSON Response karena ini endpoint yang sering dipanggil via AJAX.
     */
    public function delete(int $id): \Illuminate\Http\JsonResponse
    {
        try {
            $item = $this->productService->find($id);

            $this->productService->delete($item);

            return JsonResponseHelper::success("Produk $item->name telah dihapus.");
        } catch (ModelNotFoundException $e) {
            return JsonResponseHelper::error($e->getMessage(), 404);
        } catch (ModelInUseException $e) {
            return JsonResponseHelper::error($e->getMessage(), 409);
        } catch (\Exception $e) {
            Log::error("Gagal menghapus produk $id.", ['exception' => $e]);
            return JsonResponseHelper::error("Terdapat kesalahan saat menhapus produk.", 500, $e);
        }
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
