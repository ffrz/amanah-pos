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

use Carbon\Carbon;

/**
 * StockMovement Model
 */
class StockMovement extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     * @var array
     */
    protected $fillable = [
        'product_id',
        'ref_id',
        'ref_type',
        'quantity',
        'created_at',
        'created_by',
    ];

    /**
     * Reference types.
     */
    const RefType_InitialStock     = 'initial_stock';
    const RefType_ManualAdjustment = 'manual_adjustment';
    const RefType_StockAdjustment  = 'stock_adjustment';
    const RefType_SalesOrderDetail          = 'sales_order';
    const RefType_SalesOrderDetailReturn    = 'sales_order_return';
    const RefType_PurchaseDetailOrder       = 'purchase_order';
    const RefType_PurchaseOrderDetailReturn = 'purchase_order_return';

    const RefTypes = [
        self::RefType_InitialStock     => 'Stok Awal',
        self::RefType_ManualAdjustment => 'Penyesuaian Manual',
        self::RefType_StockAdjustment  => 'Penyesuaian Stok',
        self::RefType_SalesOrderDetail          => 'Order Penjualan',
        self::RefType_SalesOrderDetailReturn    => 'Retur Penjualan',
        self::RefType_PurchaseDetailOrder       => 'Pembelian',
        self::RefType_PurchaseOrderDetailReturn => 'Retur Order Pembelian',
    ];

    protected $appends = [
        'formatted_id',
    ];

    protected function casts(): array
    {
        return [
            'product_id' => 'integer',
            'ref_id'     => 'integer',
            'ref_type'   => 'string',
            'quantity'   => 'decimal:3',
            'created_at' => 'datetime',
            'created_by' => 'integer',
        ];
    }

    public function getFormattedIdAttribute()
    {
        return Setting::value('stock_movement_code_prefix', 'SM-')
            . Carbon::parse($this->created_at)->format('Ymd')
            . '-'
            . $this->id;
    }

    // Define the ref relationship dynamically based on ref_type and ref_id
    public function ref()
    {
        // Check ref_type and return the corresponding related model
        switch ($this->ref_detail_type) {
            case 'sales_order_detail':
                return $this->belongsTo(SalesOrderDetail::class, 'ref_detail_id');
            case 'sales_order_return_detail':
                return $this->belongsTo(SalesOrderReturnDetail::class, 'ref_detail_id');
            case 'purchase_order_detail':
                return $this->belongsTo(PurchaseOrderDetail::class, 'ref_detail_id');
            case 'purchase_order_return_detail':
                return $this->belongsTo(PurchaseOrderReturnDetail::class, 'ref_detail_id');
            case 'stock_adjustment_detail':
                return $this->belongsTo(StockAdjustmentDetail::class, 'ref_detail_id');

            default:
                return null;
        }
    }
}
