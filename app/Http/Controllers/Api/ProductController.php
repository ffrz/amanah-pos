<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\ProductService;
use App\Http\Requests\Api\Product\GetProductsRequest;
use App\Http\Requests\Api\Product\SaveProductRequest; // Import SaveProductRequest
use Illuminate\Http\JsonResponse;
use App\Http\Resources\ProductResource;

// Hapus StoreProductRequest dan UpdateProductRequest karena sudah tidak digunakan
// use App\Http\Requests\Api\Product\StoreProductRequest;
// use App\Http\Requests\Api\Product\UpdateProductRequest;

class ProductController extends Controller
{
    protected $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    /**
     * Display a listing of products with search, filter, and pagination capabilities.
     *
     * @param  \App\Http\Requests\Api\Product\GetProductsRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(GetProductsRequest $request): JsonResponse
    {
        $filters = $request->validated();
        $perPage = $filters['per_page'] ?? 10;

        // Ekstrak opsi pengurutan dari filters untuk diteruskan ke ProductService
        $options = [
            'order_by' => $filters['order_by'] ?? null,
            'order_type' => $filters['order_type'] ?? null,
        ];

        // Memanggil ProductService untuk mendapatkan query builder
        $productsQuery = $this->productService->getProductsQuery($filters, $options);

        // Paginasi hasil
        $products = $productsQuery->paginate($perPage);

        // Menggunakan ProductResource::collection untuk mengembalikan data dengan format yang konsisten
        return ProductResource::collection($products)->response();
    }

    /**
     * Display the specified product by its ID.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Product $product): JsonResponse
    {
        // Product sudah di-load oleh Route Model Binding.
        // Eager load relasi yang diperlukan untuk resource
        $product->load(['category', 'supplier']);

        // Mengembalikan single ProductResource
        return (new ProductResource($product))->response();
    }

    /**
     * Display the specified product by barcode or product code.
     *
     * @param  string  $identifier
     * @return \Illuminate\Http\JsonResponse
     */
    public function findByBarcode(string $identifier): JsonResponse
    {
        // Menggunakan ProductService untuk menemukan produk
        $product = $this->productService->findProductByIdentifier($identifier);

        if (!$product) {
            return response()->json([
                'status' => 'error',
                'message' => 'Product not found.',
                'data' => null,
            ], 404);
        }

        // Eager load relasi jika diperlukan untuk resource
        $product->load(['category', 'supplier']);

        // Mengembalikan single ProductResource
        return (new ProductResource($product))->response();
    }

    /**
     * Store a newly created product in storage.
     *
     * @param  \App\Http\Requests\Api\Product\SaveProductRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(SaveProductRequest $request): JsonResponse
    {
        try {
            // Memanggil saveProduct tanpa instance produk untuk membuat yang baru
            $product = $this->productService->saveProduct($request->validated());
            $product->load(['category', 'supplier']); // Eager load untuk response resource

            // Mengembalikan single ProductResource dengan status 201 Created
            return (new ProductResource($product))
                ->response()
                ->setStatusCode(201)
                ->header('Location', route('products.show', $product->id)); // Opsional: Tambahkan header Location
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create product: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified product in storage.
     *
     * @param  \App\Http\Requests\Api\Product\SaveProductRequest  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(SaveProductRequest $request, Product $product): JsonResponse
    {
        try {
            // Memanggil saveProduct dengan instance produk untuk memperbarui
            $updatedProduct = $this->productService->saveProduct($request->validated(), $product);
            $updatedProduct->load(['category', 'supplier']); // Eager load untuk response resource

            // Mengembalikan single ProductResource
            return (new ProductResource($updatedProduct))->response();
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update product: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified product from storage.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Product $product): JsonResponse
    {
        try {
            $productName = $product->name; // Simpan nama sebelum dihapus
            $this->productService->deleteProduct($product);

            // Mengembalikan response 204 No Content
            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete product: ' . $e->getMessage(),
            ], 500);
        }
    }
}
