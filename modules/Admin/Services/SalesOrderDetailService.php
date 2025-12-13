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
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\SalesOrderDetail;
use Illuminate\Support\Facades\DB;

class SalesOrderDetailService
{
    public function __construct(
        protected ProductService $productService
    ) {}

    public function findItemOrFail($id): SalesOrderDetail
    {
        return SalesOrderDetail::with(['order'])->findOrFail($id);
    }

    private function ensureOrderIsEditable(SalesOrder $order)
    {
        if ($order->status != SalesOrder::Status_Draft) {
            throw new BusinessRuleViolationException('Order sudah tidak dapat diubah.');
        }
    }

    public function addItem(SalesOrder $order, array $data): SalesOrderDetail
    {
        $this->ensureOrderIsEditable($order);

        // Ambil input kode (bisa id, kode produk, atau barcode)
        $code = $data['product_code'] ?? $data['id'] ?? null;
        $uom  = $data['uom'] ?? null;

        // Hasil scan berupa array: ['product' => ..., 'uom' => ..., 'unit' => ...]
        $scanResult = $this->productService->scanProduct($code, $uom);
        $product = $scanResult['product'];
        $scannedUom = $scanResult['uom'];     // Misal: "ROLL"
        $scannedUnit = $scanResult['unit'];   // Model ProductUnit (jika ada)

        $quantity = $data['qty'] ?? 1;

        // 2. TENTUKAN HARGA BERDASARKAN HASIL SCAN
        $targetPriceType = 'price_1';
        if ($order->customer) {
            $targetPriceType = $order->customer->default_price_type ?? 'price_1';
        }

        // Logic Harga:
        // Jika yang discan adalah Unit (Roll), ambil harga dari model Unit.
        // Jika yang discan adalah Base (Meter), ambil harga dari model Product.
        if ($scannedUnit) {
            $price = $scannedUnit->getSellingPrice($targetPriceType);
        } else {
            $price = $product->getSellingPrice($targetPriceType);
        }

        // Override harga manual (jika diizinkan)
        if ($product->price_editable && isset($data['price']) && is_numeric($data['price'])) {
            $price = (float) $data['price'];
        }

        // 3. HITUNG MODAL & KONVERSI (Pakai Helper yang sudah kita buat)
        // Helper ini akan otomatis hitung cost ROLL dan conversion rate 305
        $unitData = $this->resolveUnitData($product, $scannedUom);

        // 4. LOGIKA MERGE (GABUNG ITEM)
        $merge = $data['merge'] ?? false;
        $item = null;

        if ($merge) {
            $item = SalesOrderDetail::where('order_id', $order->id)
                ->where('product_id', $product->id)
                ->where('product_uom', $scannedUom) // PENTING: Hanya gabung jika UOM sama!
                ->first();
        }

        if ($item) {
            // Update Item Existing
            $order->total_cost  -= $item->subtotal_cost;
            $order->total_price -= $item->subtotal_price;

            $item->quantity += $quantity;
            // $item->user_input_qty += $quantity; (Opsional: abaikan sesuai diskusi sebelumnya)

            // Harga dan Cost biasanya tidak diupdate saat merge (ikut harga awal masuk),
            // kecuali ada requirement khusus.

            $item->updateTotals();
        } else {
            // Create New Item
            $item = new SalesOrderDetail([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_barcode' => $product->barcode, // Barcode utama produk (atau mau simpan barcode unit?)

                // DATA PENTING MULTI SATUAN
                'product_uom'     => $scannedUom,            // "ROLL"
                'quantity'        => $quantity,              // 1
                'conversion_rate' => $unitData['conversion_rate'], // 305
                'cost'            => $unitData['cost'],      // Modal per ROLL

                'price'           => $price,                 // Harga per ROLL
                'notes'           => '',
            ]);
            $item->updateTotals();
        }

        return DB::transaction(function () use ($order, $item) {
            $item->save();
            $order->updateGrandTotal();
            $order->save();
            return $item;
        });
    }

    public function updateItem(SalesOrderDetail $item, array $data): void
    {
        // 1. Load Relasi Penting
        $order = $item->order;
        $product = $item->product;

        $this->ensureOrderIsEditable($order);

        // 2. Revert (Kurangi) Total Order lama sebelum item diedit
        // Kita keluarkan dulu nilai item ini dari total order
        $order->total_cost  -= $item->subtotal_cost;
        $order->total_price -= $item->subtotal_price;

        // 3. Ambil Data Input Baru
        $newQty = $data['qty'] ?? 0;
        $newUom = $data['uom'] ?? $item->product_uom; // Pakai uom lama jika tidak dikirim
        $newPrice = $data['price'] ?? $item->price;    // Pakai price lama jika tidak dikirim

        // 4. HITUNG ULANG MODAL & KONVERSI (The Core Logic)
        // Kita panggil helper tadi untuk mendapatkan cost & rate yang valid
        $unitData = $this->resolveUnitData($product, $newUom);

        // 5. Update Attribute Item
        $item->fill([
            'quantity'        => $newQty,
            'product_uom'     => $newUom,

            // Update data krusial dari helper
            'conversion_rate' => $unitData['conversion_rate'],
            'cost'            => $unitData['cost'],

            'notes'           => $data['notes'] ?? $item->notes,
        ]);

        // Validasi harga (cegah harga null/negatif)
        if ($newPrice !== null && $newPrice >= 0) {
            $item->price = $newPrice;
        }

        // 6. Simpan & Hitung Ulang Total
        DB::transaction(function () use ($order, $item) {
            // Method ini ada di Model, menghitung:
            // subtotal_cost = quantity * cost (yang baru)
            // subtotal_price = quantity * price (yang baru)
            $item->updateTotals();
            $item->save();

            // Hitung ulang Grand Total Order
            $order->updateGrandTotal();
            $order->save();
        });
    }

    public function deleteItem(SalesOrderDetail $item): void
    {
        /**
         * @var SalesOrder
         */
        $order = $item->order;

        $this->ensureOrderIsEditable($order);

        DB::transaction(function () use ($order, $item) {
            $item->delete();

            $order->updateGrandTotal();
            $order->save();
        });
    }

    /**
     * Helper untuk mencari data konversi dan cost berdasarkan UOM baru.
     * Mengembalikan array ['conversion_rate', 'cost']
     */
    private function resolveUnitData(Product $product, string $targetUom): array
    {
        // 1. Cek apakah targetnya adalah Satuan Dasar?
        if ($targetUom === $product->uom) {
            return [
                'conversion_rate' => 1,
                'cost'            => $product->cost,
            ];
        }

        // 2. Jika bukan dasar, cari di tabel product_units
        $unit = $product->productUnits()
            ->where('name', $targetUom)
            ->first();

        if ($unit) {
            // Hitung Cost per Satuan Konversi
            // Logika: Jika di tabel unit ada cost khusus (>0), pakai itu.
            // Jika tidak, hitung otomatis: Cost Dasar * Faktor Konversi
            $calculatedCost = ($unit->cost > 0)
                ? $unit->cost
                : ($product->cost * $unit->conversion_factor);

            return [
                'conversion_rate' => $unit->conversion_factor,
                'cost'            => $calculatedCost,
            ];
        }

        // 3. Fallback (Safety Net): Jika satuan tidak ditemukan (aneh),
        // kembalikan ke satuan dasar agar tidak error/crash.
        return [
            'conversion_rate' => 1,
            'cost'            => $product->cost,
        ];
    }
}
