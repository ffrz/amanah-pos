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
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class PurchaseOrderReturnDetailService
{
    public function __construct(
        protected ProductService $productService
    ) {}

    // OK
    public function findItemOrFail($id): PurchaseOrderDetail
    {
        return PurchaseOrderDetail::with(['return'])->findOrFail($id);
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
        $total_purchase_quantity = PurchaseOrderDetail::where('order_id', $purchase_order_id)
            ->whereNull('return_id')
            ->where('product_id', $product_id)
            ->sum('quantity');

        $total_returned_quantity = PurchaseOrderDetail::where('product_id', $product_id)
            ->join('purchase_order_returns as por', 'por.id', '=', 'purchase_order_details.return_id')
            ->where('por.purchase_order_id', $purchase_order_id)
            ->where('por.status', 'closed')
            ->whereNull('por.deleted_at')
            ->sum('purchase_order_details.quantity');

        return $total_purchase_quantity - $total_returned_quantity;
    }

    // OK
    public function addItem(PurchaseOrderReturn $orderReturn, array $data): PurchaseOrderDetail
    {
        $this->ensureOrderIsEditable($orderReturn);

        $merge = $data['merge'] ?? false;
        $product = $this->productService->findProductByCodeOrId($data);
        $orderDetail = PurchaseOrderDetail::where('order_id', $orderReturn->purchase_order_id)
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

        /**
         * @var PurchaseOrderDetail
         */
        $item = null;
        if ($merge) {
            // kalo gabung cari rekaman yang sudah ada
            $item = PurchaseOrderDetail::where('return_id', '=', $orderReturn->id)
                ->where('product_id', '=', $product->id)
                ->get()
                ->first();
        }

        if ($item) {
            $orderReturn->total_cost -= $item->subtotal_cost;
            $item->quantity += $quantity;
            $item->updateTotals();
        } else {
            $item = new PurchaseOrderDetail([
                'order_id' => $orderReturn->purchase_order_id,
                'return_id' => $orderReturn->id,
                'product_id' => $orderDetail->product_id,
                'product_name' => $orderDetail->product_name,
                'product_barcode' => $orderDetail->product_barcode ?? '',
                'product_uom' => $orderDetail->product_uom,
                'quantity' => $quantity,
                'cost' => $orderDetail->cost,
                'subtotal_cost' => $quantity * $orderDetail->cost,
                // 'price' => $price,
                // 'subtotal_price' => $quantity * $price,
                'notes' => '',
            ]);
        }

        if ($item->quantity > $maxQuantity) {
            throw new BusinessRuleViolationException('Kwantitas retur melebihi batas.');
        }

        return DB::transaction(function () use ($orderReturn, $item) {
            $item->save();

            $orderReturn->updateGrandTotal();
            $orderReturn->save();

            return $item;
        });
    }

    // OK
    public function updateItem(PurchaseOrderDetail $item, array $data): void
    {
        /**
         * @var PurchaseOrderReturn
         */
        $order = $item->return;
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
        $item->updateTotals();
        $item->notes = $data['notes'] ?? '';

        DB::transaction(function () use ($order, $item) {
            $item->save();
            $order->updateGrandTotal();
            $order->save();
        });
    }

    // OK
    public function deleteItem(PurchaseOrderDetail $item): void
    {
        /**
         * @var PurchaseOrderReturn
         */
        $order = $item->return;

        $this->ensureOrderIsEditable($order);

        DB::transaction(function () use ($order, $item) {
            $item->delete();
            $order->updateGrandTotal();
            $order->save();
        });
    }
}
