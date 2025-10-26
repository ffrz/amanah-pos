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

    public function addItem(PurchaseOrder $order, array $data, bool $merge = false): PurchaseOrderDetail
    {
        $this->ensureOrderIsEditable($order);

        $product  = $this->productService->findProductByCodeOrId($data);
        $quantity = $data['qty'] ?? 0;
        $cost     = $data['cost'] ?? $product->cost;
        $item   = null;

        // untuk opsi gabungkan item
        if ($merge) {
            $item = PurchaseOrderDetail::where('order_id', '=', $order->id)
                ->where('product_id', '=', $product->id)
                ->get()
                ->first();
        }

        if ($item) {
            // increment quantity jika digabung
            $item->addQuantity($quantity);
        } else {
            $item = new PurchaseOrderDetail([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_barcode' => $product->barcode,
                'product_uom' => $product->uom,
                'quantity' => $quantity,
                'cost' => $cost,
                'subtotal_cost' => $quantity * $cost,
                'notes' => '',
            ]);
        }

        return DB::transaction(function () use ($item, $order) {
            $item->save();
            $order->save();
            return $item;
        });
    }

    public function updateItem(PurchaseOrderDetail $item, array $data): void
    {
        /**
         * @var PurchaseOrder $order
         */
        $order = $item->order;

        $this->ensureOrderIsEditable($order);

        $item->quantity = $data['qty'] ?? 0.;
        $item->cost     = $data['cost'] ?? null;
        $item->notes    = $data['notes'] ?? '';

        DB::transaction(function () use ($item, $order) {
            $item->updateTotals();
            $item->save();

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

            $order->save();
        });
    }
}
