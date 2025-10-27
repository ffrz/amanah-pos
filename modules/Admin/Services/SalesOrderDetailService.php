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

        $merge = $data['merge'] ?? false;
        $product = $this->productService->findProductByCodeOrId($data);
        $quantity = $data['qty'] ?? 1;
        $data['price'] = $data['price'] ?? null;
        $price = null;

        if ($order->customer) {
            $price_type = $order->customer->default_price_type;
            $price = $product->getAttribute($price_type);
        }

        if (!$price) {
            $price = $product->price_1;
        }

        if ($product->price_editable && $data['price'] !== null) {
            $price = $data['price'];
        }

        $item = null;
        if ($merge) {
            // kalo gabung cari rekaman yang sudah ada
            $item = SalesOrderDetail::where('order_id', '=', $order->id)
                ->where('product_id', '=', $product->id)
                ->get()
                ->first();
        }

        if ($item) {
            // kurangi dulu dengan subtotal sebelum hitungan baru
            $order->total_cost  -= $item->subtotal_cost;
            $order->total_price -= $item->subtotal_price;

            // kalau sudah ada cukup tambaih qty saja
            $item->quantity += $quantity;

            // perbarui subtotal
            $item->subtotal_cost  = $item->cost  * $item->quantity;
            $item->subtotal_price = $item->price * $item->quantity;
        } else {
            $item = new SalesOrderDetail([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_barcode' => $product->barcode,
                'product_uom' => $product->uom,
                'quantity' => $quantity,
                'cost' => $product->cost,
                'subtotal_cost' => $quantity * $product->cost,
                'price' => $price,
                'subtotal_price' => $quantity * $price,
                'notes' => '',
            ]);
        }

        return DB::transaction(function () use ($order, $item) {
            $item->save();

            $order->updateTotals();
            $order->save();

            return $item;
        });
    }

    public function updateItem(SalesOrderDetail $item, array $data): void
    {
        /**
         * @var SalesOrder
         */
        $order = $item->order;

        $this->ensureOrderIsEditable($order);

        $order->total_cost  -= $item->subtotal_cost;
        $order->total_price -= $item->subtotal_price;

        $item->quantity = $data['qty'] ?? 0;

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

    public function deleteItem(SalesOrderDetail $item): void
    {
        /**
         * @var SalesOrder
         */
        $order = $item->order;

        $this->ensureOrderIsEditable($order);

        DB::transaction(function () use ($order, $item) {
            $item->delete();

            $order->updateTotals();
            $order->save();
        });
    }
}
