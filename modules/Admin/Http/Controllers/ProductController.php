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
use App\Http\Requests\Product\GetProductsRequest;
use App\Http\Requests\Product\SaveProductRequest;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Supplier;
use App\Services\CommonDataService;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ProductController extends Controller
{
    protected $productService;
    protected $commonDataService;

    public function __construct(ProductService $productService, CommonDataService $commonDataService) // Inject CommonDataService
    {
        $this->productService = $productService;
        $this->commonDataService = $commonDataService;
    }

    public function index()
    {
        return Inertia::render('product/Index', [
            'categories' => $this->commonDataService->getProductCategories(),
            'suppliers' => $this->commonDataService->getSuppliers(),
        ]);
    }

    public function detail($id = 0)
    {
        $item = $this->productService->findProductById($id);

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
     * @param \App\Http\Requests\Api\Product\GetProductsRequest $request
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function data(GetProductsRequest $request)
    {
        $options = $request->validated();
        $productsQuery = $this->productService->getProductsQuery($options['filter'] ?? [], $options);
        return $productsQuery->paginate($options['per_page'] ?? 10)->withQueryString();
    }

    public function duplicate($id)
    {
        $item = $this->productService->findProductById($id);

        if (!$item) {
            return redirect()->route('admin.product.index')->with('error', 'Product to duplicate not found.');
        }

        // Kloning item dan null-kan ID untuk dianggap baru
        $item->id = null;
        $item->exists = false; // Penting agar Eloquent tahu ini objek baru

        return Inertia::render('product/Editor', [
            'data' => $item,
            'categories' => $this->commonDataService->getProductCategories(),
            'suppliers' => $this->commonDataService->getSuppliers(),
        ]);
    }

    public function editor($id = 0)
    {
        $item = $id ? $this->productService->findProductById($id) : new Product(
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
     * @param \App\Http\Requests\Api\Product\SaveProductRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save(SaveProductRequest $request) // Menggunakan SaveProductRequest
    {
        try {
            $validatedData = $request->validated();
            $validatedData['description'] = $validatedData['description'] ?? '';
            $validatedData['barcode'] = $validatedData['barcode'] ?? '';
            $validatedData['notes'] = $validatedData['notes'] ?? '';
            $validatedData['uom'] = $validatedData['uom'] ?? '';

            $product = null;

            // Jika ada ID dalam request, coba temukan produk yang ada
            if ($request->has('id') && $request->input('id')) {
                $product = $this->productService->findProductById($request->input('id'));
                if (!$product) {
                    return redirect()->back()->with('error', 'Produk tidak ditemukan.');
                }
            }

            // Memanggil saveProduct di ProductService untuk create atau update
            $item = $this->productService->saveProduct($validatedData, $product);

            return redirect(route('admin.product.index'))
                ->with('success', "Produk $item->name telah disimpan.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Operation failed: ' . $e->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            $item = $this->productService->findProductById($id);
            if (!$item) {
                return redirect()->back()->with('error', 'Product tidak ditemukan.');
            }

            if ($this->productService->isProductUsedInTransactions($item)) {
                return redirect()->back()->with('error', 'Produk ini tidak dapat dihapus karena sedang digunakan dalam transaksi.');
            }
            $this->productService->deleteProduct($item);

            return redirect()->route('admin.product.index')
                ->with('success', "Produk $item->name telah dihapus.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete product: ' . $e->getMessage());
        }
    }

    public function import(Request $request)
    {
        if ($request->getMethod() === Request::METHOD_POST) {
            // 1. Validasi file yang diunggah
            $request->validate([
                'csv_file' => 'required|mimes:csv,txt|max:10240',
            ]);

            $file = $request->file('csv_file');

            // 2. Buka file untuk dibaca
            if (($handle = fopen($file->getRealPath(), 'r')) !== false) {

                // Dapatkan header dari baris pertama untuk pemetaan kolom
                $header = fgetcsv($handle, 1000, ',');

                // Mulai transaksi database untuk memastikan data konsisten
                DB::beginTransaction();

                try {
                    // Loop melalui setiap baris data
                    while (($data = fgetcsv($handle, 1000, ',')) !== false) {

                        // Pastikan baris data tidak kosong
                        if (empty(array_filter($data, 'strlen'))) {
                            continue;
                        }

                        // 3. Gabungkan header dan data untuk membuat array yang mudah diakses
                        $row = array_combine($header, $data);

                        // 4. Proses relasi: Kategori dan Supplier
                        $category = ProductCategory::firstOrCreate([
                            'name' => trim($row['category']),
                        ]);

                        $supplier = Supplier::firstOrCreate([
                            'name' => trim($row['supplier']),
                        ]);

                        // 5. Siapkan data produk
                        $productData = [

                            'description' => trim($row['description']),
                            'cost'        => $row['cost'],
                            'price'       => $row['price'],
                            'uom'         => $row['uom'],
                            'stock'       => $row['stock'],
                            'category_id' => $category->id,
                            'supplier_id' => $supplier->id,
                            'type'        => $row['type'] ?? Product::Type_Stocked,
                        ];

                        // 6. Simpan data produk
                        Product::firstOrCreate([
                            'barcode' => $row['barcode'],
                            'name'    => trim($row['name']),
                        ], $productData);
                    }

                    // Commit transaksi jika semua baris berhasil
                    DB::commit();

                    fclose($handle);
                    return redirect()->back()->with('success', 'Data produk berhasil diimpor!');
                } catch (\Exception $e) {
                    // Rollback transaksi jika terjadi kesalahan
                    DB::rollBack();
                    fclose($handle);

                    return redirect()->back()->with('error', 'Gagal mengimpor data: ' . $e->getMessage());
                }

                return redirect()->back()->with('error', 'Gagal membuka file.');
            }
        }

        return inertia('product/Import');
    }
}
