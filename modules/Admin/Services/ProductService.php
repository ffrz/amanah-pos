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
use App\Exceptions\ModelNotModifiedException;
use App\Helpers\WhatsAppHelper;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Setting;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\UserActivityLog;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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
        $query = Product::query()->with([
            'supplier' => function ($query) {
                $query->select('id', 'code', 'name');
            },
            'category' => function ($query) {
                $query->select('id', 'name');
            },
        ])
            ->select([
                'id',
                'type', // product browser butuh jenis untuk nampilin stok
                'supplier_id',
                'category_id',
                'name',
                'cost',
                'uom',
                'stock',
                'price_1',
                'price_2',
                'price_3',
                'active',
            ]);

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
                $query->whereColumn('stock', '<', 'min_stock');
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

        $paginator = $query->paginate($options['per_page'])->withQueryString();

        $paginator->getCollection()->each(function ($customer) {
            $customer->makeHidden([
                'type_label',
            ]);
        });

        return $paginator;
    }

    public function duplicate($id): Product
    {
        $product =  $this->find($id)->replicate();
        $product->name .= ' (COPY)';
        return $product;
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
        return $id ? $this->find($id) : new Product([
            'active' => 1,
            'type' => Product::Type_Stocked,
            'name' => $this->generateProductCode(),
        ]);
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
    public function save(Product $item, array $data): Product
    {

        $isNew = !$item->id;
        $oldStock = $item->stock;
        $oldData = $isNew ? [] : $item->toArray();

        $item->fill($data);

        if (empty($item->getDirty())) {
            throw new ModelNotModifiedException();
        }

        return DB::transaction(function () use ($item, $oldStock, $oldData, $isNew) {
            $item->save();

            $newStock = $item->stock;

            if ($item->type === Product::Type_Stocked) {
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
        });
    }

    /**
     * Delete a product.
     *
     * @param \App\Models\Product $item
     * @return Product
     * @throws \Exception
     */
    public function delete(Product $item): Product
    {
        if ($item->isUsedInTransactions()) {
            throw new ModelInUseException("Produk tidak dapat dihapus karena sudah digunakan di transaksi.");
        }

        return DB::transaction(function () use ($item) {

            // Opsional: Hapus juga StockMovement terkait jika diperlukan
            // StockMovement::where('product_id', $item->id)->delete();

            $item->delete();

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

            return $item;
        });
    }

    public function import($file)
    {
        $separator = ',';
        if (($handle = fopen($file->getRealPath(), 'r')) === false) {
            return false;
        }

        $first_line = fgets($handle, 5000);
        if (!$first_line) {
            fclose($handle);
            throw new Exception('Header tidak terdeteksi');
        }

        $comma_count = substr_count($first_line, ',');
        $semicolon_count = substr_count($first_line, ';');
        if ($semicolon_count > $comma_count) {
            $separator = ';';
        }

        rewind($handle);
        $header = fgetcsv($handle, 5000, $separator);

        return DB::transaction(function () use ($handle, $header, $separator) {

            while (($data = fgetcsv($handle, 5000, $separator)) !== false) {
                if (empty(array_filter($data, 'strlen'))) {
                    continue;
                }

                // Gabungkan header dan data untuk membuat array yang mudah diakses
                // if (count($header) != count($data)) {
                //     dd($header, $data);
                // }
                $row = array_combine($header, $data);

                // Proses relasi: Kategori dan Supplier
                $category = null;
                if (!empty($row['category'])) {
                    $category = ProductCategory::firstOrCreate([
                        'name' => trim($row['category']),
                    ]);
                }

                $supplier = null;
                if (!empty($row['supplier'])) {
                    $supplier = Supplier::firstOrCreate([
                        'name' => trim($row['supplier']),
                    ], [
                        'code' => app(SupplierService::class)->generateSupplierCode()
                    ]);
                }

                // Simpan data produk
                $product = Product::create([
                    'name'        => trim($row['name']),
                    'barcode'     => $row['barcode'] ?? '',
                    'description' => trim($row['description'] ?? ''),
                    'cost'        => $row['cost'] ?? 0,
                    'price_1'     => $row['price'] ?? 0,
                    'uom'         => $row['uom'] ?? '',
                    'stock'       => $row['stock'] ?? 0,
                    'category_id' => $category ? $category->id : null,
                    'supplier_id' => $supplier ? $supplier->id : null,
                    'type'        => $row['type'] ?? Product::Type_Stocked,
                    'active'      => $row['active'] ?? 1,
                ]);

                if ($row['stock'] > 0) {
                    StockMovement::create([
                        'product_id'      => $product->id,
                        'product_name'    => $product->name,
                        'uom'             => $product->uom,
                        'ref_id'          => null,
                        'ref_type'        => StockMovement::RefType_InitialStock,
                        'quantity'        => $row['stock'],
                        'quantity_before' => 0,
                        'quantity_after'  => $row['stock'],
                        'notes'           => "Stok awal dari import",
                    ]);
                }
            }

            $this->userActivityLogService->log(
                UserActivityLog::Category_Product,
                UserActivityLog::Name_Product_Import,
                "Produk berhasil diimport."
            );

            fclose($handle);

            return true;
        });
    }

    public function sendPriceList($customer_ids, string $message): array
    {
        $customers = Customer::whereIn('id', $customer_ids)->get(['id', 'name', 'phone']);

        if ($customers->isEmpty()) {
            throw new Exception("Tidak ada pelanggan yang valid untuk dikirimi pesan");
        }

        $results = [];

        foreach ($customers as $customer) {
            try {
                $response = WhatsAppHelper::sendMessage($customer->phone, $message);

                $results[] = [
                    'customer_id' => $customer->id,
                    'name'        => $customer->name,
                    'phone'       => $customer->phone,
                    'status'      => $response['status'] ? 'success' : 'failed',
                    'provider'    => $response['data'] ?? $response['message'] ?? null,
                ];

                // bisa log ke database bila perlu (misal tabel wa_logs)
            } catch (Exception $e) {
                Log::error("Gagal kirim WA ke {$customer->phone}: " . $e->getMessage());

                $results[] = [
                    'customer_id' => $customer->id,
                    'name'        => $customer->name,
                    'phone'       => $customer->phone,
                    'status'      => 'error',
                    'error'       => $e->getMessage(),
                ];
            }
        }

        return [
            'total' => count($results),
            'success' => collect($results)->where('status', 'success')->count(),
            'failed' => collect($results)->where('status', '!=', 'success')->count(),
            'data' => $results,
        ];
    }

    public function addToStock(Product $product, $quantity)
    {
        $lockedProduct = Product::where('id', $product->id)->lockForUpdate()->firstOrFail();
        $lockedProduct->stock += $quantity;
        $lockedProduct->save();
    }

    public function findProductByCodeOrId(array $data)
    {
        $product = null;
        $productCode = $data['product_code'] ?? null;
        $productId = $data['product_id'] ?? null;

        if ($productId) {
            $product = Product::find($productId);
        } elseif ($productCode) {
            // cari berdasarkan barcode
            $product = Product::where('barcode', '=', $productCode)->first();

            // kalo belum ketemu cari berdasarkan nama produk
            if (!$product) {
                $product = Product::where('name', '=', $productCode)->first();
            }
        }

        if (!$product) {
            throw new ModelNotFoundException('Produk tidak ditemukan.');
        }

        return $product;
    }

    private function generateProductCode(): string
    {
        $lastId = Product::max('id') ?? 0;
        $nextId = $lastId + 1;
        $code = str_pad($nextId, 5, '0', STR_PAD_LEFT);
        $prefix = Setting::value('product.code-prefix', 'P-');
        return $prefix . $code;
    }
}
