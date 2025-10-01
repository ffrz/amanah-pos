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

namespace Modules\Admin\Services;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\StockMovement;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
    public function find(int $id): ?Product
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
     * @param \App\Models\Product $product Product instance to save.
     * @param array $data Data for the product.
     * @return bool|array
     */
    public function save(Product $product, array $data): bool|array
    {
        $oldStock = $product->stock;
        $isNewProduct = !!$product->id;

        $product->fill($data);

        $changedFields = $product->getDirty();

        if (empty($changedFields)) {
            return false;
        }

        $product->save();

        $newStock = $product->stock;

        if (isset($data['stock']) && $product->type === Product::Type_Stocked) {
            $refType = $isNewProduct ? StockMovement::RefType_InitialStock : StockMovement::RefType_ManualAdjustment;
            $quantityBefore = $isNewProduct ? 0 : $oldStock;
            $quantity = $isNewProduct ? $newStock : $newStock - $oldStock;

            if ($quantityBefore != $newStock) {
                StockMovement::create([
                    'ref_type' => $refType,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'uom' => $product->uom,
                    'quantity_before' => $quantityBefore,
                    'quantity_after' => $newStock,
                    'quantity' => $quantity,
                    'notes' => $isNewProduct ? 'Stok awal' : 'Edit stok manual',
                ]);
            }
        }

        return $changedFields;
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

    public function import($file)
    {
        try {
            if (($handle = fopen($file->getRealPath(), 'r')) === false) {
                return false;
            }

            $header = fgetcsv($handle, 1000, ',');

            DB::beginTransaction();

            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                if (empty(array_filter($data, 'strlen'))) {
                    continue;
                }

                // Gabungkan header dan data untuk membuat array yang mudah diakses
                $row = array_combine($header, $data);

                // Proses relasi: Kategori dan Supplier
                $category = ProductCategory::firstOrCreate([
                    'name' => trim($row['category']),
                ]);

                $supplier = Supplier::firstOrCreate([
                    'name' => trim($row['supplier']),
                ]);

                // Siapkan data produk
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

                // Simpan data produk
                Product::firstOrCreate([
                    'barcode' => $row['barcode'],
                    'name'    => trim($row['name']),
                ], $productData);
            }

            DB::commit();
            fclose($handle);
            Log::info('Import produk berhasil.');
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            fclose($handle);
            Log::error('Gagal mengimpor data produk.', $e);
        }

        return false;
    }
}
