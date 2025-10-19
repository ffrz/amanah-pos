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
use App\Models\SalesOrderDetail;
use App\Models\SalesOrderReturn;
use App\Models\SalesOrderReturnDetail;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class SalesOrderReturnDetailService
{
    public function __construct(
        protected ProductService $productService
    ) {}

    // OK
    public function findItemOrFail($id): SalesOrderReturnDetail
    {
        return SalesOrderReturnDetail::with(['parent'])->findOrFail($id);
    }

    // OK
    private function ensureOrderIsEditable(SalesOrderReturn $order)
    {
        if ($order->status != SalesOrderReturn::Status_Draft) {
            throw new BusinessRuleViolationException('Order sudah tidak dapat diubah.');
        }
    }

    // TODO: CHECK THIS!!!
    private function getMaxQuantity($sales_order_id, $product_id)
    {
        $total_sales_quantity = SalesOrderDetail::where('parent_id', $sales_order_id)
            ->where('product_id', $product_id)
            ->sum('quantity');

        $total_returned_quantity = SalesOrderReturnDetail::where('product_id', $product_id)
            ->join('sales_order_returns as sor', 'sor.id', '=', 'sales_order_return_details.sales_order_return_id')
            ->where('sor.sales_order_id', $sales_order_id)
            ->whereNull('sor.deleted_at')
            ->where('sor.status', 'closed')
            ->sum('sales_order_return_details.quantity');

        return $total_sales_quantity - $total_returned_quantity;
    }

    // OK
    public function addItem(SalesOrderReturn $orderReturn, array $data): SalesOrderReturnDetail
    {
        $this->ensureOrderIsEditable($orderReturn);

        $merge = $data['merge'] ?? false;
        $product = $this->productService->findProductByCodeOrId($data);
        $orderDetail = SalesOrderDetail::where('parent_id', $orderReturn->sales_order_id)
            ->where('product_id', $product->id)
            ->first();
        $maxQuantity = $this->getMaxQuantity($orderReturn->sales_order_id, $product->id);

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
            $item = SalesOrderReturnDetail::where('sales_order_return_id', '=', $orderReturn->id)
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
            $returnDetail = new SalesOrderReturnDetail([
                'sales_order_return_id' => $orderReturn->id,
                'product_id' => $orderDetail->product_id,
                'product_name' => $orderDetail->product_name,
                'product_barcode' => $orderDetail->product_barcode,
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
    public function updateItem(SalesOrderReturnDetail $item, array $data): void
    {
        /**
         * @var SalesOrderReturn
         */
        $order = $item->parent;
        $maxQuantity = $this->getMaxQuantity($order->sales_order_id, $item->product_id);

        $this->ensureOrderIsEditable($order);

        $order->total_cost  -= $item->subtotal_cost;
        $order->total_price -= $item->subtotal_price;

        $item->quantity = $data['qty'] ?? 0;

        if ($item->quantity <= 0 || $item->quantity > $maxQuantity) {
            throw new BusinessRuleViolationException('Kwantitas tidak valid atau melebihi batas.');
        }

        $price = $data['price'] ?? null;
        if ($price !== null && $price >= 0) {
            $item->price = $price;
        }

        // perbarui subtotal
        $item->subtotal_cost  = $item->cost  * $item->quantity;
        $item->subtotal_price = $item->price * $item->quantity;
        $item->notes = $data['notes'] ?? '';

        DB::transaction(function () use ($order, $item) {
            $item->save();
            $order->updateTotals();
            $order->save();
        });
    }

    // OK
    public function deleteItem(SalesOrderReturnDetail $item): void
    {
        /**
         * @var SalesOrderReturn
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
