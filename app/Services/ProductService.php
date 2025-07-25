<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ProductService
{
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
            if ($filters['type'] == Product::Type_Stocked) {
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

        $orderBy = $options['order_by'] ?? 'name';
        $orderType = $options['order_type'] ?? 'asc';
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
        return Product::with(['category', 'supplier', 'createdBy', 'updatedBy'])->find($id);
    }

    /**
     * Find a product by barcode or product code.
     *
     * @param string $identifier
     * @return \App\Models\Product|null
     */
    public function findProductByIdentifier(string $identifier): ?Product
    {
        $product = Product::with(['category', 'supplier'])
            ->where('barcode', $identifier)
            ->first();

        if (!$product) {
            $product = Product::with(['category', 'supplier'])
                ->where('kode_barang', $identifier) // Assuming 'kode_barang' column exists
                ->first();
        }
        return $product;
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
        DB::beginTransaction();
        try {
            $product = Product::create($data);

            if (isset($data['stock']) && $data['stock'] !== null) {
                StockMovement::create([
                    'ref_type' => StockMovement::RefType_InitialStock,
                    'product_id' => $product->id,
                    'quantity' => $data['stock'],
                    'user_id' => auth()->id(),
                ]);
            }

            DB::commit();
            return $product;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
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
        DB::beginTransaction();
        try {
            $oldStock = $product->stock;
            $product->update($data);
            $newStock = $product->stock;

            if ($oldStock != $newStock) {
                $diff = $newStock - $oldStock;
                StockMovement::create([
                    'ref_type' => StockMovement::RefType_ManualAdjustment,
                    'product_id' => $product->id,
                    'quantity' => $diff,
                    'user_id' => auth()->id(),
                ]);
            }

            DB::commit();
            return $product;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
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
}
