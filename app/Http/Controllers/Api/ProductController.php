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

// KELAS INI HANYA CONTOH API, TIDAK BENAR-BENAR DIGUNAKNA DI APP

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

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Modules\Admin\Services\ProductService;

use Illuminate\Http\JsonResponse;
use App\Http\Resources\ProductResource;
use App\Models\ProductUnit;
use Modules\Admin\Http\Requests\Product\SaveRequest;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(protected ProductService $productService) {}

    /**
     * Display a listing of products with search, filter, and pagination capabilities.
     *
     * @param  \App\Http\Requests\Api\Product\Re  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->all();
        $perPage = $filters['per_page'] ?? 10;

        // Ekstrak opsi pengurutan dari filters untuk diteruskan ke ProductService
        $options = [
            'order_by' => $filters['order_by'] ?? null,
            'order_type' => $filters['order_type'] ?? null,
        ];

        // Memanggil ProductService untuk mendapatkan query builder
        $productsQuery = $this->productService->getData([
            'filter' => [],
            'order_by' => 'name',
            'order_type' => 'asc',
            'per_page' => 10,
        ]);

        // Paginasi hasil
        // $products = $productsQuery->paginate($perPage);

        // Menggunakan ProductResource::collection untuk mengembalikan data dengan format yang konsisten
        return ProductResource::collection($productsQuery)->response();
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
    public function store(SaveRequest $request): JsonResponse
    {
        try {
            // Memanggil saveProduct tanpa instance produk untuk membuat yang baru
            $product = $this->productService->save($request->validated());
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

    /**
     * Get available units for a specific product.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUnits(Product $product): JsonResponse
    {
        $units = [];

        $units[] = [
            'name' => $product->uom ?? 'PCS',
            'cost' => $product->cost ?? 0,
            'price_1' => $product->price_1,
            'price_2' => $product->price_2 ?? $product->price_1,
            'price_3' => $product->price_3 ?? $product->price_2 ?? $product->price_1,
            'is_base_unit' => true,
        ];

        if (method_exists($product, 'productUnits')) {
            $productUnits = ProductUnit::query()
                ->where('product_id', $product->id)
                ->where('is_base_unit', false)
                ->orderBy('conversion_factor', 'asc')
                ->select('name', 'price_1', 'price_2', 'price_3', 'cost', 'conversion_factor')
                ->get();

            foreach ($productUnits as $unit) {
                $units[] = [
                    'name' => $unit->name,
                    'conversion_factor' => $unit->conversion_factor,
                    'cost' => $unit->cost ?? $product->cost * $unit->conversion_factor,
                    'price_1' => $unit->price_1,
                    'price_2' => $unit->price_2 ?? $unit->price_1,
                    'price_3' => $unit->price_3 ?? $unit->price_2 ?? $unit->price_1,
                    'is_base_unit' => false,
                ];
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => $units
        ]);
    }
}
