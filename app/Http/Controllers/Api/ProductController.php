<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\ProductService; // Import ProductService
use App\Http\Requests\Api\Product\GetProductsRequest;
use App\Http\Requests\Api\Product\StoreProductRequest; // Asumsi Anda membuat ini
use App\Http\Requests\Api\Product\UpdateProductRequest; // Asumsi Anda membuat ini
use Illuminate\Http\JsonResponse;
// use App\Http\Resources\ProductResource; // Impor jika Anda menggunakan API Resources

class ProductController extends Controller
{
    protected $productService;

    // Inject ProductService melalui constructor
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

        // Memanggil ProductService untuk mendapatkan query builder
        $productsQuery = $this->productService->getProductsQuery($filters, $filters); // Melewatkan filters dan options yang sama

        $items = $productsQuery->paginate($perPage);

        // Transformasi deskripsi tetap di sini jika hanya untuk output API ini
        $items->getCollection()->transform(function ($item) {
            $item->description = strlen($item->description) > 50 ? substr($item->description, 0, 50) . '...' : $item->description;
            return $item;
        });

        // If using ProductResource, uncomment this
        // return ProductResource::collection($items);

        return response()->json([
            'data' => $items->items(),
            'current_page' => $items->currentPage(),
            'per_page' => $items->perPage(),
            'total' => $items->total(),
            'last_page' => $items->lastPage(),
        ]);
    }

    /**
     * Display the specified product by its ID.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Product $product): JsonResponse
    {
        // Product sudah di-load oleh Route Model Binding, service hanya untuk memastikan relasi
        $product = $this->productService->findProductById($product->id);

        if (!$product) {
            return response()->json([
                'status' => 'error',
                'message' => 'Product not found.',
                'data' => null,
            ], 404);
        }

        // If using ProductResource, uncomment this
        // return new ProductResource($product);

        return response()->json([
            'status' => 'success',
            'message' => 'Product detail fetched successfully.',
            'data' => $product,
        ]);
    }

    /**
     * Display the specified product by barcode or product code.
     *
     * @param  string  $identifier
     * @return \Illuminate\Http\JsonResponse
     */
    public function showByBarcode(string $identifier): JsonResponse
    {
        $product = $this->productService->findProductByIdentifier($identifier);

        if ($product) {
            // If using ProductResource, uncomment this
            // return new ProductResource($product);

            return response()->json([
                'status' => 'success',
                'message' => 'Product found.',
                'data' => $product,
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Product not found.',
            'data' => null,
        ], 404);
    }

    /**
     * Store a newly created product in storage.
     *
     * @param  \App\Http\Requests\Api\Product\StoreProductRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        try {
            $product = $this->productService->createProduct($request->validated());
            $product->load(['category', 'supplier']); // Eager load for response

            // If using ProductResource, uncomment this
            // return new ProductResource($product);

            return response()->json([
                'status' => 'success',
                'message' => __("messages.product-created", ['name' => $product->name]),
                'data' => $product,
            ], 201);
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
     * @param  \App\Http\Requests\Api\Product\UpdateProductRequest  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        try {
            $updatedProduct = $this->productService->updateProduct($product, $request->validated());
            $updatedProduct->load(['category', 'supplier']); // Eager load for response

            // If using ProductResource, uncomment this
            // return new ProductResource($updatedProduct);

            return response()->json([
                'status' => 'success',
                'message' => __("messages.product-updated", ['name' => $updatedProduct->name]),
                'data' => $updatedProduct,
            ]);
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
            $productName = $product->name;
            $this->productService->deleteProduct($product);

            return response()->json([
                'status' => 'success',
                'message' => __("messages.product-deleted", ['name' => $productName]),
                'data' => null,
            ], 204);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete product: ' . $e->getMessage(),
            ], 500);
        }
    }
}
