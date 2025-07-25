<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Supplier;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    protected $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function index()
    {
        return Inertia::render('admin/product/Index', [
            'categories' => ProductCategory::all(['id', 'name']),
            'suppliers' => Supplier::all(['id', 'name', 'phone']),
        ]);
    }

    public function detail($id = 0)
    {
        $item = $this->productService->findProductById($id);

        if (!$item) {
            return redirect()->route('admin.product.index')
                ->with('error', 'Produk tidak ditemukan.');
        }

        return Inertia::render('admin/product/Detail', [
            'data' => $item,
        ]);
    }

    public function data(Request $request)
    {
        $filters = $request->all();

        $productsQuery = $this->productService->getProductsQuery($filters, $filters);

        return $productsQuery->paginate($request->get('per_page', 10))->withQueryString();
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

        return Inertia::render('admin/product/Editor', [
            'data' => $item,
            'categories' => ProductCategory::all(['id', 'name']),
            'suppliers' => Supplier::all(['id', 'name', 'phone']),
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

        return Inertia::render('admin/product/Editor', [
            'data' => $item,
            'categories' => ProductCategory::all(['id', 'name']),
            'suppliers' => Supplier::all(['id', 'name', 'phone']),
        ]);
    }

    public function save(Request $request)
    {
        $validatedData = $request->validate([
            'category_id' => ['nullable', Rule::exists('product_categories', 'id')],
            'supplier_id' => ['nullable', Rule::exists('suppliers', 'id')],
            'type' => ['nullable', Rule::in(array_keys(Product::Types))],
            'name' => [
                'required',
                'max:255',
                Rule::unique('products', 'name')->ignore($request->id),
            ],
            'description' => 'nullable|max:1000',
            'barcode' => 'nullable|max:255',
            'uom' => 'nullable|max:255',
            'stock' => 'nullable|numeric',
            'min_stock' => 'nullable|numeric',
            'max_stock' => 'nullable|numeric',
            'cost' => 'nullable|numeric',
            'price' => 'nullable|numeric',
            'active' => 'nullable|boolean',
            'notes' => 'nullable|max:1000',
        ]);

        try {
            if ($request->id) {
                $item = $this->productService->findProductById($request->id);
                if (!$item) {
                    return redirect()->back()->with('error', 'Produk tidak ditemukan.');
                }
                $item = $this->productService->updateProduct($item, $validatedData);
            } else {
                $item = $this->productService->createProduct($validatedData);
            }

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
            if ($item->isUsedInTransactions()) {
                return redirect()->back()->with('error', 'Produk ini tidak dapat dihapus karena sedang digunakan dalam transaksi.');
            }
            $this->productService->deleteProduct($item);

            return redirect()->route('admin.product.index')
                ->with('success', "Produk $item->name telah dihapus.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete product: ' . $e->getMessage());
        }
    }

    private function findProductById($id)
    {
        return Product::find($id);
    }
}
