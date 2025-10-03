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

use App\Exceptions\ModelInUseException;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\UserActivityLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductService
{
    public function __construct(
        protected UserActivityLogService $userActivityLogService,
        protected DocumentVersionService $documentVersionService,
    ) {}
    /**
     * Get a query builder for products with various filters and options.
     *
     * @param  array  $filter Array of filters (e.g., search, category_id, type, stock_status, status)
     * @param  array  $options Array of options (e.g., order_by, order_type)
     * @return LengthAwarePaginator
     */
    public function getData(array $options): LengthAwarePaginator
    {
        $filter = $options['filter'];
        $query = Product::query()->with(['supplier', 'category']);

        if (isset($filter['search']) && $filter['search']) {
            $query->where(function ($q) use ($filter) {
                $q->where('name', 'like', '%' . $filter['search'] . '%');
                $q->orWhere('description', 'like', '%' . $filter['search'] . '%');
                $q->orWhere('notes', 'like', '%' . $filter['search'] . '%');
                $q->orWhere('barcode', 'like', '%' . $filter['search'] . '%');
            });
        }

        if (isset($filter['type']) && is_array($filter['type'])) {
            $types = $filter['type'];
            $query->where(function ($q) use ($types) {
                foreach ($types as $type) {
                    $q->orWhere('type', '=', $type);
                }
            });
        }

        if (isset($filter['stock_status']) && $filter['stock_status'] != 'all') {
            if ($filter['stock_status'] == 'low') {
                $query->whereColumn('stock', '<', 'min_stock')->where('stock', '!=', 0);
            } elseif ($filter['stock_status'] == 'out') {
                $query->where('stock', '=', 0);
            } elseif ($filter['stock_status'] == 'over') {
                $query->whereColumn('stock', '>', 'max_stock');
            } elseif ($filter['stock_status'] == 'ready') {
                $query->where('stock', '>', 0);
            }
        }

        // Filter by category
        if (isset($filter['category_id']) && is_array($filter['category_id'])) {
            $categories = $filter['category_id'];
            $query->where(function ($q) use ($categories) {
                foreach ($categories as $category) {
                    $q->orWhere('category_id', '=', $category);
                }
            });
        }

        // Filter by supplier
        if (isset($filter['supplier_id']) && $filter['supplier_id'] != 'all') {
            $suppliers = $filter['supplier_id'];
            $query->where(function ($q) use ($suppliers) {
                foreach ($suppliers as $supplier) {
                    $q->orWhere('supplier_id', '=', $supplier);
                }
            });
        }

        // Filter by active status
        if (isset($filter['status']) && ($filter['status'] == 'active' || $filter['status'] == 'inactive')) {
            $query->where('active', '=', $filter['status'] == 'active');
        }

        $query->orderBy($options['order_by'], $options['order_type']);

        return $query->paginate($options['per_page'])->withQueryString();
    }

    /**
     * Find a product by its ID.
     *
     * @param int $id
     * @return \App\Models\Product|null
     */
    public function find(int $id): ?Product
    {
        return Product::with(['category', 'supplier', 'creator', 'updater'])->findOrFail($id);
    }

    public function findOrCreate($id = null)
    {
        return $id ? $this->find($id) : new Product(
            ['active' => 1, 'type' => Product::Type_Stocked]
        );
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
     * @param \App\Models\Product $item Product instance to save.
     * @param array $data Data for the product.
     * @return Product
     */
    public function save(array $data): bool|Product
    {
        $item = $this->findOrCreate($data['id']);
        $isNew = !$item->id;
        $oldStock = $item->stock;
        $oldData = $isNew ? [] : $item->toArray();

        $item->fill($data);

        if (empty($item->getDirty())) {
            return false;
        }

        $item->save();

        $newStock = $item->stock;

        if (isset($data['stock']) && $item->type === Product::Type_Stocked) {
            $refType = $isNew ? StockMovement::RefType_InitialStock : StockMovement::RefType_ManualAdjustment;
            $quantityBefore = $isNew ? 0 : $oldStock;
            $quantity = $isNew ? $newStock : $newStock - $oldStock;

            if ($quantityBefore != $newStock) {
                StockMovement::create([
                    'ref_type' => $refType,
                    'product_id' => $item->id,
                    'product_name' => $item->name,
                    'uom' => $item->uom,
                    'quantity_before' => $quantityBefore,
                    'quantity_after' => $newStock,
                    'quantity' => $quantity,
                    'notes' => $isNew ? 'Stok awal' : 'Edit stok manual',
                ]);
            }
        }

        $this->documentVersionService->createVersion($item);

        if ($isNew) {
            $this->userActivityLogService->log(
                UserActivityLog::Category_Product,
                UserActivityLog::Name_Product_Create,
                "Produk $item->name berhasil dibuat.",
                [
                    'formatter' => 'product',
                    'data' => $item->toArray(),
                ],
            );
        } else {
            $this->userActivityLogService->log(
                UserActivityLog::Category_Product,
                UserActivityLog::Name_Product_Update,
                "Produk $item->name berhasil diperbarui.",
                [
                    'formatter' => 'product',
                    'new_data' => $item->toArray(),
                    'old_data' => $oldData,
                ],
            );
        }

        return $item;
    }

    /**
     * Delete a product.
     *
     * @param \App\Models\Product $item
     * @return bool|null
     * @throws \Exception
     */
    public function delete(Product $item): ?bool
    {
        if ($item->isUsedInTransactions()) {
            throw new ModelInUseException("Produk tidak dapat dihapus karena sudah digunakan di transaksi.");
        }

        try {
            DB::beginTransaction();

            // Opsional: Hapus juga StockMovement terkait jika diperlukan
            // StockMovement::where('product_id', $item->id)->delete();

            $result = $item->delete();

            $this->documentVersionService->createDeletedVersion($item);

            $this->userActivityLogService->log(
                UserActivityLog::Category_Product,
                UserActivityLog::Name_Product_Delete,
                "Produk $item->name berhasil dihapus.",
                [
                    'formatter' => 'product',
                    'data' => $item->toArray(),
                ],
            );

            DB::commit();

            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
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
                $itemData = [
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
                ], $itemData);
            }

            $this->userActivityLogService->log(
                UserActivityLog::Category_Product,
                UserActivityLog::Name_Product_Import,
                "Produk berhasil diimport."
            );

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
