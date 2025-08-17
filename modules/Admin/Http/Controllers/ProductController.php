<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Product\GetProductsRequest;
use App\Http\Requests\Api\Product\SaveProductRequest; // Import SaveProductRequest
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Supplier;
use App\Services\CommonDataService;
use App\Services\ProductService;
use Illuminate\Http\Request; // Masih diperlukan untuk method 'data' jika GetProductsRequest tidak digunakan
use Inertia\Inertia;
// use Illuminate\Validation\Rule; // Tidak lagi diperlukan karena validasi dipindahkan ke SaveProductRequest

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
            'categories' => $this->commonDataService->getCategories(),
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
            'categories' => $this->commonDataService->getCategories(),
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
            'categories' => $this->commonDataService->getCategories(),
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
}
