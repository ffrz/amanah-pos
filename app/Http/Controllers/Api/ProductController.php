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

        // --- 1. SIAPKAN VARIABEL HARGA DASAR DULU (Casting ke float) ---
        $baseCost   = (float) ($product->cost ?? 0);
        $basePrice1 = (float) ($product->price_1 ?? 0);
        $basePrice2 = (float) ($product->price_2 ?? 0);
        $basePrice3 = (float) ($product->price_3 ?? 0);

        // --- 2. LOGIKA PENENTUAN HARGA (Fallback Chain) ---

        // Jika Price 2 nol, pakai Price 1
        $finalPrice2 = ($basePrice2 > 0) ? $basePrice2 : $basePrice1;

        // Jika Price 3 nol, cek Price 2. Jika Price 2 nol juga, pakai Price 1
        // (Cara singkat: cek Price 3, kalau 0 pakai hasil finalPrice2)
        $finalPrice3 = ($basePrice3 > 0) ? $basePrice3 : $finalPrice2;

        $units[] = [
            'name' => $product->uom ?? 'PCS',
            'cost' => $baseCost,
            'price_1' => $basePrice1,
            'price_2' => $finalPrice2, // Sudah aman dari nilai 0
            'price_3' => $finalPrice3, // Sudah aman dari nilai 0
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
                $unitCost   = (float) $unit->cost;
                $conversion = (float) $unit->conversion_factor;

                // Ambil harga unit, pastikan float
                $uPrice1 = (float) $unit->price_1;
                $uPrice2 = (float) $unit->price_2;
                $uPrice3 = (float) $unit->price_3;

                // Logika Fallback Unit
                $finalUnitPrice2 = ($uPrice2 > 0) ? $uPrice2 : $uPrice1;
                $finalUnitPrice3 = ($uPrice3 > 0) ? $uPrice3 : $finalUnitPrice2;

                $units[] = [
                    'name' => $unit->name,
                    'conversion_factor' => $conversion,
                    'cost' => ($unitCost > 0) ? $unitCost : ($baseCost * $conversion),
                    'price_1' => $uPrice1,
                    'price_2' => $finalUnitPrice2,
                    'price_3' => $finalUnitPrice3,
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
