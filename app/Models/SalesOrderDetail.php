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

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class SalesOrderDetail extends BaseModel
{
    use SoftDeletes;

    protected $fillable = [
        'order_id',
        'return_id',
        'product_id',
        'product_name',
        'product_barcode',
        'product_description',
        'product_uom',      // Satuan Transaksi (misal: ROLL)

        'quantity',         // Kuantitas Transaksi (misal: 2) -> Dipakai hitung subtotal

        // --- FIELD BARU DARI SCHEMA ---
        // 'user_input_qty', TIDAK DIBUTUHKAN, BUTUH DIBUANG SAJA!
        // 'user_input_uom', TIDAK DIBUTUHKAN, BUTUH DIBUANG SAJA!
        'conversion_rate',  // Nilai konversi saat itu (misal: 305.00)
        // ------------------------------

        'cost',             // Modal per Satuan Transaksi (misal: Modal per Roll)
        'subtotal_cost',
        'price',            // Harga per Satuan Transaksi (misal: Harga per Roll)
        'subtotal_price',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'order_id'        => 'integer',
            'return_id'       => 'integer',
            'product_id'      => 'integer',
            'product_name'    => 'string',
            'product_uom'     => 'string',
            'product_barcode' => 'string',
            'quantity'        => 'decimal:3',
            'cost'            => 'decimal:2',
            'price'           => 'decimal:2',
            'subtotal_cost'   => 'decimal:2',
            'subtotal_price'  => 'decimal:2',
            'notes'           => 'string',

            'discount_amount'   => 'decimal:2',
            'discount_percent'  => 'decimal:2',
            'subtotal_discount' => 'decimal:2',
        ];
    }

    protected function getQuantityAttribute(string $value): float
    {
        return (float) $value;
    }

    protected function getCostAttribute(string $value): float
    {
        return (float) $value;
    }

    protected function getPriceAttribute(string $value): float
    {
        return (float) $value;
    }

    protected function getSubtotalCostAttribute(string $value): float
    {
        return (float) $value;
    }

    protected function getSubtotalPriceAttribute(string $value): float
    {
        return (float) $value;
    }

    public function order()
    {
        return $this->belongsTo(SalesOrder::class, 'order_id');
    }

    public function return()
    {
        return $this->belongsTo(SalesOrderReturn::class, 'return_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function updateTotals()
    {
        $this->subtotal_cost  = $this->cost  * $this->quantity;
        $this->subtotal_price = $this->price * $this->quantity;
    }
}
