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
use App\Models\PurchaseOrderDetail;
use App\Models\PurchaseOrderReturn;
use App\Models\PurchaseOrderReturnDetail;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class PurchaseOrderReturnDetailService
{
    public function __construct(
        protected ProductService $productService
    ) {}

    // OK
    public function findItemOrFail($id): PurchaseOrderReturnDetail
    {
        return PurchaseOrderReturnDetail::with(['parent'])->findOrFail($id);
    }

    // OK
    private function ensureOrderIsEditable(PurchaseOrderReturn $order)
    {
        if ($order->status != PurchaseOrderReturn::Status_Draft) {
            throw new BusinessRuleViolationException('Order sudah tidak dapat diubah.');
        }
    }

    // TODO: CHECK THIS!!!
    private function getMaxQuantity($purchase_order_id, $product_id)
    {
        $total_purchase_quantity = PurchaseOrderDetail::where('parent_id', $purchase_order_id)
            ->where('product_id', $product_id)
            ->sum('quantity');

        $total_returned_quantity = PurchaseOrderReturnDetail::where('product_id', $product_id)
            ->join('purchase_order_returns as por', 'por.id', '=', 'purchase_order_return_details.purchase_order_return_id')
            ->where('por.purchase_order_id', $purchase_order_id)
            ->where('por.status', 'closed')
            ->sum('purchase_order_return_details.quantity');

        return $total_purchase_quantity - $total_returned_quantity;
    }

    // OK
    public function addItem(PurchaseOrderReturn $orderReturn, array $data): PurchaseOrderReturnDetail
    {
        $this->ensureOrderIsEditable($orderReturn);

        $merge = $data['merge'] ?? false;
        $product = $this->productService->findProductByCodeOrId($data);
        $orderDetail = PurchaseOrderDetail::where('parent_id', $orderReturn->purchase_order_id)
            ->where('product_id', $product->id)
            ->first();
        $maxQuantity = $this->getMaxQuantity($orderReturn->purchase_order_id, $product->id);

        if (!$orderDetail) {
            throw new ModelNotFoundException('Item tidak ditemukan');
        }

        $quantity = $data['qty'] ?? 1;
        $data['price'] = $data['price'] ?? null;
        $price = null;

        if (!$price) {
            $price = $orderDetail->price;
        }

        if ($product->price_editable && $data['price'] !== null) {
            $price = $data['price'];
        }

        $item = null;
        if ($merge) {
            // kalo gabung cari rekaman yang sudah ada
            $item = PurchaseOrderReturnDetail::where('purchase_order_return_id', '=', $orderReturn->id)
                ->where('product_id', '=', $product->id)
                ->get()
                ->first();
        }

        if ($item) {
            // kurangi dulu dengan subtotal sebelum hitungan baru
            $orderReturn->total_cost  -= $item->subtotal_cost;
            $orderReturn->total_price -= $item->subtotal_price;

            // kalau sudah ada cukup tambaih qty saja
            $item->quantity += $quantity;

            // perbarui subtotal
            $item->subtotal_cost  = $item->cost  * $item->quantity;
            $item->subtotal_price = $item->price * $item->quantity;
        } else {
            $returnDetail = new PurchaseOrderReturnDetail([
                'purchase_order_return_id' => $orderReturn->id,
                'product_id' => $orderDetail->product_id,
                'product_name' => $orderDetail->product_name,
                'product_barcode' => $orderDetail->product_barcode ?? '',
                'product_uom' => $orderDetail->product_uom,
                'quantity' => $quantity,
                'cost' => $orderDetail->cost,
                'subtotal_cost' => $quantity * $orderDetail->cost,
                'price' => $price,
                'subtotal_price' => $quantity * $price,
                'notes' => '',
            ]);
        }

        if ($returnDetail->quantity > $maxQuantity) {
            throw new BusinessRuleViolationException('Kwantitas retur melebihi batas.');
        }

        return DB::transaction(function () use ($orderReturn, $returnDetail) {
            $returnDetail->save();

            $orderReturn->updateTotals();
            $orderReturn->save();

            return $returnDetail;
        });
    }

    // OK
    public function updateItem(PurchaseOrderReturnDetail $item, array $data): void
    {
        /**
         * @var PurchaseOrderReturn
         */
        $order = $item->parent;
        $maxQuantity = $this->getMaxQuantity($order->purchase_order_id, $item->product_id);

        $this->ensureOrderIsEditable($order);

        $order->total_cost  -= $item->subtotal_cost;

        $item->quantity = $data['qty'] ?? 0;

        if ($item->quantity <= 0 || $item->quantity > $maxQuantity) {
            throw new BusinessRuleViolationException('Kwantitas tidak valid atau melebihi batas.');
        }

        // FIXME: kalau bukan price berarti cost
        $cost = $data['cost'] ?? $data['price'] ?? null;
        if ($cost !== null && $cost >= 0) {
            $item->cost = $cost;
        }

        // perbarui subtotal
        $item->subtotal_cost  = $item->cost  * $item->quantity;
        $item->notes = $data['notes'] ?? '';

        DB::transaction(function () use ($order, $item) {
            $item->save();
            $order->updateTotals();
            $order->save();
        });
    }

    // OK
    public function deleteItem(PurchaseOrderReturnDetail $item): void
    {
        /**
         * @var PurchaseOrderReturn
         */
        $order = $item->parent;

        $this->ensureOrderIsEditable($order);

        DB::transaction(function () use ($order, $item) {
            $item->delete();
            $order->updateTotals();
            $order->save();
        });
    }
}
