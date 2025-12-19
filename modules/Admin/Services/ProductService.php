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

use App\Exceptions\BusinessRuleViolationException;
use App\Exceptions\ModelInUseException;
use App\Exceptions\ModelNotModifiedException;
use App\Helpers\WhatsAppHelper;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductQuantityPrice;
use App\Models\ProductUnit;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
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
            'supplier:id,code,name',
            'category:id,name',
            'productUnits' => fn($q) => $q->where('is_base_unit', false)->orderBy('conversion_factor', 'asc')
        ])
            ->select([
                'id',
                'type',
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
                'barcode',
                'min_stock',
                'max_stock'
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

        if (isset($filter['sales_order_id'])) {
            $query->whereIn('id', function ($sub) use ($filter) {
                $sub->select('product_id')
                    ->from('sales_order_details')
                    ->where('order_id', $filter['sales_order_id']);
            });
        }

        if (isset($filter['purchase_order_id'])) {
            $query->whereIn('id', function ($sub) use ($filter) {
                $sub->select('product_id')
                    ->from('purchase_order_details')
                    ->where('order_id', $filter['purchase_order_id']);
            });
        }

        // Filter by active status
        if (isset($filter['status']) && ($filter['status'] == 'active' || $filter['status'] == 'inactive')) {
            $query->where('active', '=', $filter['status'] == 'active');
        }

        $query->orderBy($options['order_by'], $options['order_type']);

        $paginator = $query->paginate($options['per_page'])->withQueryString();

        $paginator->getCollection()->transform(function ($product) {

            // 1. Logika Stok Pecahan (Mirip method find, tapi versi ringkas)
            // Kita butuh unit urut DESC (Besar -> Kecil) khusus hitung stok
            $calcUnits = $product->productUnits->sortByDesc('conversion_factor');
            $remainder = $product->stock;
            $parts = [];

            foreach ($calcUnits as $unit) {
                if ($unit->conversion_factor > 0 && $remainder >= $unit->conversion_factor) {
                    $qty = floor($remainder / $unit->conversion_factor);
                    if ($qty > 0) {
                        $parts[] = number_format($qty, 0, ',', '.') . ' ' . $unit->name;
                        $remainder = fmod($remainder, $unit->conversion_factor);
                    }
                }
            }

            // Sisa satuan dasar
            if ($remainder > 0.0001 || count($parts) === 0) {
                $fmt = (float)$remainder == (int)$remainder
                    ? number_format($remainder, 0, ',', '.')
                    : number_format($remainder, 2, ',', '.');
                $parts[] = $fmt . ' ' . $product->uom;
            }

            // Inject attribute baru ke JSON response
            $product->stock_breakdown_text = implode(', ', $parts);

            return $product;
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
        return Product::with(['category:id,name', 'supplier:id,code,name', 'creator:id,username,name', 'updater:id,username,name', 'productUnits'])->findOrFail($id);
    }

    // TODO: AI generated code, we still need to review this!
    public function findWithStockBreakdown(int $id): ?Product
    {
        // 1. Load Relasi untuk dikirim ke Frontend (Tetap ASC / Kecil ke Besar)
        // Agar tabel harga tetap rapi (m -> Roll -> Dus)
        $product = Product::with([
            'category:id,name',
            'supplier:id,code,name',
            'creator:id,username,name',
            'updater:id,username,name',
            'productUnits' => fn($q) => $q->where('is_base_unit', false)
                ->orderBy('conversion_factor', 'asc')
        ])->findOrFail($id);

        // --- LOGIC HITUNG PECAHAN STOK (FIXED) ---
        $remainder = $product->stock;
        $parts = [];

        // 2. CRITICAL FIX: Kita buat salinan list unit lalu urutkan DESC (Besar ke Kecil)
        // khusus untuk perhitungan matematika ini saja.
        $calculationUnits = $product->productUnits->sortByDesc('conversion_factor');

        foreach ($calculationUnits as $unit) {
            // Pastikan faktor konversi valid & sisa stok cukup untuk minimal 1 unit ini
            if ($unit->conversion_factor > 0 && $remainder >= $unit->conversion_factor) {

                $qty = floor($remainder / $unit->conversion_factor);

                if ($qty > 0) {
                    $parts[] = number_format($qty, 0, ',', '.') . ' ' . $unit->name;

                    // Kurangi sisa stok
                    // Gunakan fmod untuk akurasi desimal, atau pengurangan biasa
                    $remainder = fmod($remainder, $unit->conversion_factor);
                }
            }
        }

        // 3. Masukkan Sisa Stok (Satuan Dasar / Terkecil)
        // Floating point precision fix: anggap 0 jika sangat kecil (misal 0.000001)
        if ($remainder > 0.0001 || count($parts) === 0) {
            $formattedRemainder = (float)$remainder == (int)$remainder
                ? number_format($remainder, 0, ',', '.')
                : number_format($remainder, 2, ',', '.');

            $parts[] = $formattedRemainder . ' ' . $product->uom;
        }

        // 4. Inject string hasil ke object
        $product->stock_breakdown = implode(', ', $parts);

        return $product;
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
            ->orWhere('name', $identifier)
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

        // Pengecekan tidak cukup dengan getDirty saja karena multi satuan harus dicek juga
        if (empty($item->getDirty()) && (!isset($data['product_units']) || empty($data['product_units']))) {
            throw new ModelNotModifiedException();
        }

        return DB::transaction(function () use ($item, $oldStock, $oldData, $isNew, $data) {
            $item->save();

            $baseUnit = ProductUnit::firstOrNew([
                'product_id'   => $item->id,
                'is_base_unit' => true,
            ]);

            // Paksa nilai Base Unit agar SAMA PERSIS dengan data tabel Products
            $baseUnit->fill([
                'name'              => $item->uom,
                'conversion_factor' => 1,
                'barcode'           => empty($item->barcode) ? null : $item->barcode,
                'price_1'           => $item->price_1,
                'price_1_markup'    => $item->price_1_markup,
                'price_1_option'    => $item->price_1_option,
                'price_2'           => $item->price_2,
                'price_2_markup'    => $item->price_2_markup,
                'price_2_option'    => $item->price_2_option,
                'price_3'           => $item->price_3,
                'price_3_markup'    => $item->price_3_markup,
                'price_3_option'    => $item->price_3_option,
            ]);

            $baseUnit->save();

            // Multi Satuan
            if (isset($data['product_units']) && is_array($data['product_units'])) {
                // Array untuk menampung ID UOM yang valid (disimpan/diupdate)
                // ID yang tidak ada di list ini nanti akan dihapus (mekanisme sync)
                $keptUomIds = [];

                foreach ($data['product_units'] as $uomData) {
                    // 1. Mapping Field JSON -> Database ProductUnit
                    $uomAttributes = [
                        'product_id'        => $item->id,
                        'name'              => $uomData['name'],
                        'conversion_factor' => $uomData['conversion_factor'],
                        'barcode'           => $uomData['barcode'] ?? null,
                        'is_base_unit'      => false, // UOM tambahan bukan base unit

                        'price_1'        => $uomData['price_1'] ?? 0,
                        'price_1_markup' => $uomData['price_1_markup'] ?? 0,
                        'price_1_option' => 'price', // belum digunakan

                        'price_2'        => $uomData['price_2'] ?? 0,
                        'price_2_markup' => $uomData['price_2_markup'] ?? 0,
                        'price_2_option' => 'price', // belum digunakan

                        'price_3'        => $uomData['price_3'] ?? 0,
                        'price_3_markup' => $uomData['price_3_markup'] ?? 0,
                        'price_3_option' => 'price', // belum digunakan
                    ];


                    // 2. Simpan atau Update ProductUnit
                    if (!empty($uomData['id'])) {
                        // Update existing
                        $unit = ProductUnit::where('id', $uomData['id'])
                            ->where('product_id', $item->id)
                            ->first();
                        if ($unit) {
                            $unit->update($uomAttributes);
                        } else {
                            // Fallback jika ID dikirim tapi tidak ketemu (jarang terjadi)
                            $unit = ProductUnit::create($uomAttributes);
                        }
                    } else {
                        // Create new
                        $unit = ProductUnit::create($uomAttributes);
                    }

                    // Simpan ID untuk proses pembersihan (deletion) nanti
                    $keptUomIds[] = $unit->id;

                    // 3. Proses Tiered Pricing (Harga Bertingkat) per Unit
                    // Kita hapus dulu tiers lama untuk unit ini agar bersih, lalu insert ulang (Full Sync)
                    $unit->quantityPrices()->forceDelete();

                    // Loop level harga 1, 2, 3
                    // TODO: Kode ini belum diuji
                    // foreach ([1, 2, 3] as $level) {
                    //     if (!empty($uomData['prices'][$level]['tiers']) && is_array($uomData['prices'][$level]['tiers'])) {
                    //         foreach ($uomData['prices'][$level]['tiers'] as $tier) {
                    //             // Validasi data tier minimal
                    //             if (isset($tier['min_qty']) && isset($tier['price'])) {
                    //                 ProductQuantityPrice::create([
                    //                     'product_unit_id' => $unit->id,
                    //                     'price_type'      => 'price_' . $level,
                    //                     'min_quantity'    => $tier['min_qty'], // Sesuaikan nama key JSON tier
                    //                     'price'           => $tier['price'],
                    //                 ]);
                    //             }
                    //         }
                    //     }
                    // }
                }

                // 4. Hapus UOM yang tidak ada di payload (User menghapus baris di UI)
                // Kecuali base unit (biasanya logic base unit terpisah, tapi amannya kita filter)
                ProductUnit::where('product_id', $item->id)
                    ->where('is_base_unit', false)
                    ->whereNotIn('id', $keptUomIds)
                    ->delete();
            }


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

    public function scanProduct($identifier, $uom = null)
    {
        // Cek berdasarkan Barcode di Unit Tambahan (Dus/Roll)
        $unitByBarcode = \App\Models\ProductUnit::with('product')
            ->where('barcode', $identifier)
            ->first();

        if ($unitByBarcode) {
            return [
                'product' => $unitByBarcode->product,
                'uom'     => $unitByBarcode->name,
                'unit'    => $unitByBarcode,
            ];
        }

        // 1.2 Cek Barcode di Produk Utama (Satuan Dasar)
        $productByBarcode = \App\Models\Product::where('barcode', $identifier)->first();

        if ($productByBarcode) {
            return [
                'product' => $productByBarcode,
                'uom'     => $productByBarcode->uom,
                'unit'    => null, // Base Unit
            ];
        }

        // Kalau gak ketemu kita cari berdasarkan nama produk (exact match)
        $product = \App\Models\Product::query()
            ->where(function ($q) use ($identifier) {
                $q->where('name', '=', $identifier);
            })
            ->first();

        if ($product) {
            // Produk ketemu sekarang kita cek user minta satuan apa?

            // KASUS A: User minta satuan spesifik (misal: "ROLL")
            if (!empty($uom)) {

                // Cek 1: Apakah yang diminta adalah Satuan Dasar?
                if (strtoupper($uom) === strtoupper($product->uom)) {
                    return [
                        'product' => $product,
                        'uom'     => $product->uom,
                        'unit'    => null,
                    ];
                }

                // Cek 2: Cari di tabel Multi Satuan (ProductUnit)
                $requestedUnit = $product->productUnits()
                    ->where('name', $uom)
                    ->first();

                if ($requestedUnit) {
                    return [
                        'product' => $product,
                        'uom'     => $requestedUnit->name,
                        'unit'    => $requestedUnit,
                    ];
                }

                throw new BusinessRuleViolationException("Satuan '$uom' tidak ditemukan.");
            }

            // KASUS B: User tidak minta satuan (atau satuan tidak ketemu), return Default/Dasar
            return [
                'product' => $product,
                'uom'     => $product->uom,
                'unit'    => null,
            ];
        }

        throw new ModelNotFoundException("Produk '$identifier' tidak ditemukan.");
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
