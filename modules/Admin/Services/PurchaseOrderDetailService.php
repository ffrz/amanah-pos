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
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use Illuminate\Support\Facades\DB;

class PurchaseOrderDetailService
{
    public function __construct(
        protected ProductService $productService
    ) {}

    public function findItemOrFail($id): PurchaseOrderDetail
    {
        return PurchaseOrderDetail::with(['order'])->findOrFail($id);
    }

    private function ensureOrderIsEditable(PurchaseOrder $order)
    {
        if ($order->status != PurchaseOrder::Status_Draft) {
            throw new BusinessRuleViolationException('Order sudah tidak dapat diubah.');
        }
    }

    /**
     * Helper untuk mendapatkan data konversi & cost standar berdasarkan UOM
     */
    private function resolveUnitData($product, $uomName)
    {
        // Default ke Satuan Dasar
        $data = [
            'uom'             => $product->uom,
            'conversion_rate' => 1,
            'cost'            => $product->cost,
        ];

        // Jika UOM yang diminta berbeda dengan UOM dasar, cari di tabel unit
        if ($uomName && $uomName !== $product->uom) {
            $unit = $product->productUnits->where('name', $uomName)->first();

            if ($unit) {
                $data['uom']             = $unit->name;
                $data['conversion_rate'] = $unit->conversion_factor;

                // Logic Default Cost untuk PO:
                // 1. Jika di unit ada cost khusus (misal harga beli grosir), pakai itu.
                // 2. Jika tidak, hitung matematis: cost dasar * konversi.
                $data['cost'] = $unit->cost > 0
                    ? $unit->cost
                    : ($product->cost * $unit->conversion_factor);
            }
        }

        return $data;
    }

    public function addItem(PurchaseOrder $order, array $data): PurchaseOrderDetail
    {
        $this->ensureOrderIsEditable($order);

        // 1. SCAN PRODUK (Sama seperti Sales Order)
        $code = $data['product_code'] ?? $data['id'] ?? null;
        $uom  = $data['uom'] ?? null;

        // Gunakan service scan yang sama
        $scanResult = $this->productService->scanProduct($code, $uom);
        $product    = $scanResult['product'];
        $scannedUom = $scanResult['uom']; // UOM yang terdeteksi (misal "DUS")

        // 2. RESOLVE DATA SATUAN (Cost & Rate)
        // Kita butuh tahu 1 DUS itu isinya berapa (rate) dan default cost-nya berapa
        $unitData = $this->resolveUnitData($product, $scannedUom);

        // 3. TENTUKAN COST FINAL
        // Prioritas: Input User > Default Unit Cost
        $inputCost = isset($data['cost']) && is_numeric($data['cost']) ? (float) $data['cost'] : null;
        $finalCost = $inputCost ?? $unitData['cost'];
        $quantity = isset($data['qty']) ? $data['qty'] : 1;

        // 4. LOGIKA MERGE (GABUNG ITEM)
        $merge = $data['merge'] ?? false;
        $item  = null;

        if ($merge) {
            $item = PurchaseOrderDetail::where('order_id', $order->id)
                ->where('product_id', $product->id)
                ->where('product_uom', $scannedUom) // PENTING: Merge hanya jika UOM sama
                ->first();
        }

        if ($item) {
            // --- UPDATE ITEM EXISTING ---

            // Kurangi total order lama dulu (untuk re-kalkulasi bersih)
            // $order->total -= $item->subtotal_cost; // gak butuh karena dibawah juga diupdate lagi dengan pemanggilan $order->updateGrandTotal()

            // Tambah quantity
            $item->quantity += $quantity;

            // Opsi: Apakah cost mau di-update mengikuti input terbaru?
            // Biasanya di PO, jika user input harga baru, harga lama ikut terupdate (rata-rata atau override).
            // Di sini kita pakai override (mengikuti harga input terakhir).
            if ($inputCost !== null) {
                $item->cost = $inputCost;
            }
        } else {
            // --- CREATE NEW ITEM ---
            $item = new PurchaseOrderDetail([
                'order_id'        => $order->id,
                'product_id'      => $product->id,
                'product_name'    => $product->name,
                'quantity'        => $quantity,
                'cost'            => $finalCost,
                'notes'           => $data['notes'] ?? '',
                'product_uom'     => $unitData['uom'],
                'conversion_rate' => $unitData['conversion_rate'],
            ]);
        }
        $item->updateTotals();

        return DB::transaction(function () use ($item, $order) {
            $item->save();
            $order->updateGrandTotal();
            $order->save();
            return $item;
        });
    }

    public function updateItem(PurchaseOrderDetail $item, array $data): void
    {
        $order   = $item->order;
        $product = $item->product;

        $this->ensureOrderIsEditable($order);

        // 1. Revert (Kurangi) Total Order lama
        // $order->total_cost -= $item->subtotal_cost; // pemanggilan $order->updateGrandTotal(); sudah otomatis merekalkulasi total jadi aman

        // 2. Ambil Input Baru
        $newQty = isset($data['qty']) ? floatval($data['qty']) : 0;
        $newUom = $data['uom'] ?? $item->product_uom;

        // Cek apakah user input cost baru
        $inputCost = isset($data['cost']) && is_numeric($data['cost']) ? (float) $data['cost'] : null;

        // 3. HITUNG ULANG RATE & COST (Jika UOM berubah)
        $unitData = $this->resolveUnitData($product, $newUom);

        // Tentukan Cost Baru:
        // A. Jika user input cost -> Pakai cost input
        // B. Jika user TIDAK input cost, TAPI Uom berubah -> Pakai default cost dari UOM baru
        // C. Jika tidak keduanya -> Pakai cost lama ($item->cost)
        if ($inputCost !== null) {
            $finalCost = $inputCost;
        } elseif ($newUom !== $item->product_uom) {
            $finalCost = $unitData['cost'];
        } else {
            $finalCost = $item->cost;
        }

        // 4. Update Attribute Item
        $item->fill([
            'quantity'        => $newQty,
            'product_uom'     => $newUom,
            'conversion_rate' => $unitData['conversion_rate'], // Penting update ini jika UOM ganti
            'cost'            => $finalCost,
            'notes'           => $data['notes'] ?? $item->notes,
        ]);

        // 5. Simpan
        DB::transaction(function () use ($item, $order) {
            $item->updateTotals(); // Recalculate subtotal_cost
            $item->save();

            $order->updateGrandTotal();
            $order->save();
        });
    }

    public function removeItem(PurchaseOrderDetail $item): void
    {
        /**
         * @var PurchaseOrder $order
         */
        $order = $item->order;

        $this->ensureOrderIsEditable($order);

        DB::transaction(function () use ($item, $order) {
            $item->delete();

            $order->updateGrandTotal();
            $order->save();
        });
    }
}
