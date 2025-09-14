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

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProductService
{
    // Definisikan konstanta untuk kolom yang diizinkan untuk pengurutan
    public const ALLOWED_SORT_COLUMNS = [
        'id',
        'name',
        'barcode',
        'price',
        'cost',
        'type',
        'created_at',
        'updated_at',
        'category_id',
        'supplier_id'
    ];

    /**
     * Get a query builder for products with various filters and options.
     *
     * @param  array  $filters Array of filters (e.g., search, category_id, type, stock_status, status)
     * @param  array  $options Array of options (e.g., order_by, order_type)
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getProductsQuery(array $filters = [], array $options = []): Builder
    {
        $query = Product::query()->with(['supplier', 'category']);

        if (isset($filters['search']) && $filters['search']) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%');
                $q->orWhere('description', 'like', '%' . $filters['search'] . '%');
                $q->orWhere('notes', 'like', '%' . $filters['search'] . '%');
                $q->orWhere('barcode', 'like', '%' . $filters['search'] . '%');
            });
        }

        if (isset($filters['type']) && $filters['type'] != 'all') {
            $query->where('type', '=', $filters['type']);
        }

        if (isset($filters['stock_status']) && $filters['stock_status'] != 'all') {
            if ($filters['stock_status'] == 'low') {
                $query->whereColumn('stock', '<', 'min_stock')->where('stock', '!=', 0);
            } elseif ($filters['stock_status'] == 'out') {
                $query->where('stock', '=', 0);
            } elseif ($filters['stock_status'] == 'over') {
                $query->whereColumn('stock', '>', 'max_stock');
            } elseif ($filters['stock_status'] == 'ready') {
                $query->where('stock', '>', 0);
            }
        }

        // Filter by category
        if (isset($filters['category_id']) && $filters['category_id'] != 'all') {
            $query->where('category_id', '=', $filters['category_id']);
        }

        // Filter by supplier
        if (isset($filters['supplier_id']) && $filters['supplier_id'] != 'all') {
            $query->where('supplier_id', '=', $filters['supplier_id']);
        }

        // Filter by active status
        if (isset($filters['status']) && ($filters['status'] == 'active' || $filters['status'] == 'inactive')) {
            $query->where('active', '=', $filters['status'] == 'active');
        }

        $orderBy = $options['order_by'] ?? 'name'; // Default sort column
        $orderType = strtolower($options['order_type'] ?? 'asc'); // Default sort type, pastikan lowercase

        // Validasi kolom pengurutan menggunakan konstanta
        if (!in_array($orderBy, self::ALLOWED_SORT_COLUMNS)) {
            $orderBy = 'name'; // Fallback ke default jika kolom tidak valid
        }

        // Validasi tipe pengurutan
        if (!in_array($orderType, ['asc', 'desc'])) {
            $orderType = 'asc'; // Fallback ke default jika tipe tidak valid
        }

        $query->orderBy($orderBy, $orderType);

        return $query;
    }

    /**
     * Find a product by its ID.
     *
     * @param int $id
     * @return \App\Models\Product|null
     */
    public function findProductById(int $id): ?Product
    {
        return Product::with(['category', 'supplier', 'creator', 'updater'])->find($id);
    }

    /**
     * Find a product by barcode or product code.
     *
     * @param string $identifier
     * @return \App\Models\Product|null
     */
    public function findProductByIdentifier(string $identifier): ?Product
    {
        return Product::with(['category', 'supplier'])
            ->where('barcode', $identifier)
            ->first();
    }

    /**
     * Create or update a product and handle stock movement.
     *
     * @param array $data Data for the product.
     * @param \App\Models\Product|null $product Optional: Product instance to update. If null, a new product will be created.
     * @return \App\Models\Product
     * @throws \Exception
     */
    public function saveProduct(array $data, ?Product $product = null): Product
    {
        DB::beginTransaction();
        try {
            $oldStock = $product ? $product->stock : 0; // Get old stock if updating, else 0 for new product

            if ($product) {
                // Update existing product
                $product->update($data);
                $isNewProduct = false;
            } else {
                // Create new product
                $product = Product::create($data);
                $isNewProduct = true;
            }

            $newStock = $product->stock;

            // Handle stock movement only if stock is provided in data and it's a stocked product
            if (isset($data['stock']) && $product->type === Product::Type_Stocked) {
                if ($isNewProduct) {
                    // Initial stock for new product
                    StockMovement::create([
                        'ref_type' => StockMovement::RefType_InitialStock,
                        'product_id' => $product->id,
                        'quantity' => $newStock, // Use newStock directly for initial
                        'user_id' => Auth::user()->id,
                    ]);
                } elseif ($oldStock != $newStock) {
                    // Manual adjustment for updated product
                    $diff = $newStock - $oldStock;
                    StockMovement::create([
                        'ref_type' => StockMovement::RefType_ManualAdjustment,
                        'product_id' => $product->id,
                        'quantity' => $diff,
                        'user_id' => Auth::user()->id,
                    ]);
                }
            }

            DB::commit();
            return $product;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Create a new product and handle stock movement.
     *
     * @param array $data
     * @return \App\Models\Product
     * @throws \Exception
     */
    public function createProduct(array $data): Product
    {
        return $this->saveProduct($data);
    }

    /**
     * Update an existing product and handle stock movement.
     *
     * @param \App\Models\Product $product
     * @param array $data
     * @return \App\Models\Product
     * @throws \Exception
     */
    public function updateProduct(Product $product, array $data): Product
    {
        return $this->saveProduct($data, $product);
    }

    /**
     * Delete a product.
     *
     * @param \App\Models\Product $product
     * @return bool|null
     * @throws \Exception
     */
    public function deleteProduct(Product $product): ?bool
    {
        DB::beginTransaction();
        try {
            // Opsional: Hapus juga StockMovement terkait jika diperlukan
            // StockMovement::where('product_id', $product->id)->delete();

            $result = $product->delete();
            DB::commit();
            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function isProductUsedInTransactions(Product $product): bool
    {
        return $product->isUsedInTransactions();
    }
}
