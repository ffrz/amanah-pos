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

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\UserActivityLog;

use Modules\Admin\Http\Requests\Product\GetDataRequest;
use Modules\Admin\Http\Requests\Product\SaveRequest;
use Modules\Admin\Services\CommonDataService;
use Modules\Admin\Services\DocumentVersionService;
use Modules\Admin\Services\ProductService;
use Modules\Admin\Services\UserActivityLogService;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function __construct(
        protected ProductService $productService,
        protected CommonDataService $commonDataService,
        protected UserActivityLogService $userActivityLogService,
        protected DocumentVersionService $documentVersionService,
    ) {}

    public function index()
    {
        return Inertia::render('product/Index', [
            'categories' => $this->commonDataService->getProductCategories(),
            'suppliers' => $this->commonDataService->getSuppliers(),
        ]);
    }

    public function detail($id = 0)
    {
        $item = $this->productService->find($id);

        if (!$item) {
            return redirect()->route('admin.product.index')
                ->with('error', 'Produk tidak ditemukan.');
        }

        return Inertia::render('product/Detail', [
            'data' => $item,
        ]);
    }

    /**
     * Get paginated product data for Inertia table.
     *
     * @param \Modules\Admin\Http\Requests\Product\GetDataRequests $request
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function data(GetDataRequest $request)
    {
        $options = $request->validated();
        $productsQuery = $this->productService->getProductsQuery($options['filter'] ?? [], $options);
        return $productsQuery->paginate($options['per_page'] ?? 10)->withQueryString();
    }

    public function duplicate($id)
    {
        $item = $this->productService->find($id);

        if (!$item) {
            return redirect()->route('admin.product.index')->with('error', 'Produk tidak ditemukan.');
        }

        $item->id = null;
        $item->exists = false;

        return Inertia::render('product/Editor', [
            'data' => $item,
            'categories' => $this->commonDataService->getProductCategories(),
            'suppliers' => $this->commonDataService->getSuppliers(),
        ]);
    }

    public function editor($id = 0)
    {
        $item = $id ? $this->productService->find($id) : new Product(
            ['active' => 1, 'type' => Product::Type_Stocked]
        );

        if ($id && !$item) {
            return redirect()->route('admin.product.index')->with('error', 'Product not found for editing.');
        }

        return Inertia::render('product/Editor', [
            'data' => $item,
            'categories' => $this->commonDataService->getProductCategories(),
            'suppliers' => $this->commonDataService->getSuppliers(),
        ]);
    }

    /**
     * Save (create or update) a product.
     *
     * @param \Modules\Admin\Http\Requests\Product\SaveRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save(SaveRequest $request)
    {
        $validated = $request->validated();
        $validated['description'] = $validated['description'] ?? '';
        $validated['barcode'] = $validated['barcode'] ?? '';
        $validated['notes'] = $validated['notes'] ?? '';
        $validated['uom'] = $validated['uom'] ?? '';

        $item = $request->id ? $this->productService->find($request->input('id')) : new Product();
        $oldValue = $item->getAttributes();

        try {
            DB::beginTransaction();

            $changes = $this->productService->save($item, $validated);
            if (!$changes) {
                DB::rollBack();
                return redirect(route('admin.product.index'))
                    ->with('success', "Tidak ada perubahan data");
            }

            // versioning
            $this->documentVersionService->createVersion($item);

            // logging
            if (!$oldValue) {
                $this->userActivityLogService->log(
                    UserActivityLog::Category_Product,
                    UserActivityLog::Name_Product_Create,
                    "Produk $item->name berhasil dibuat.",
                    $changes
                );
            } else {
                $this->userActivityLogService->log(
                    UserActivityLog::Category_Product,
                    UserActivityLog::Name_Product_Update,
                    "Produk $item->name berhasil diperbarui.",
                    $changes
                );
            }
            DB::commit();

            return redirect(route('admin.product.index'))
                ->with('success', "Produk $item->name telah disimpan.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Gagal menyimpan produk $item->id", $e);
        }

        return redirect()->back()->withInput()
            ->with('error', 'Gagal menyimpan produk.');
    }

    public function delete($id)
    {
        $item = $this->productService->find($id);
        if (!$item) {
            return redirect()->back()->with('error', 'Product tidak ditemukan.');
        }

        if ($this->productService->isProductUsedInTransactions($item)) {
            return redirect()->back()->with('error', 'Produk ini tidak dapat dihapus karena sudah digunakan dalam transaksi.');
        }

        try {
            $this->productService->deleteProduct($item);

            $this->userActivityLogService->log(
                UserActivityLog::Category_Product,
                UserActivityLog::Name_Product_Delete,
                "Produk $item->name berhasil dihapus.",
                $item->toArray(),
            );

            return redirect(route('admin.product.index'))
                ->with('success', "Produk $item->name telah dihapus.");
        } catch (\Exception $e) {
            Log::error("Gagal menghapus produk $item->id - $item->name.", $e->getMessage());
        }

        return redirect(route('admin.product.index'))
            ->with('error', "Gagal menghapus produk $item->name");
    }

    public function import(Request $request)
    {
        if ($request->getMethod() === Request::METHOD_POST) {
            $request->validate([
                'csv_file' => 'required|mimes:csv,txt|max:10240',
            ]);

            $file = $request->file('csv_file');

            if (!$this->productService->import($file)) {
                return redirect()->back()->with('error', 'Gagal mengimpor data.');
            }

            $this->userActivityLogService->log(
                UserActivityLog::Category_Product,
                UserActivityLog::Name_Product_Import,
                "Produk berhasil diimport."
            );

            return redirect()->back()->with('success', 'Data produk berhasil diimpor!');
        }

        return inertia('product/Import');
    }
}
