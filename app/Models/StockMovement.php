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
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

/**
 * StockMovement Model
 */
class StockMovement extends BaseModel
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     * @var array
     */
    protected $fillable = [
        'product_id',
        'product_name',
        'uom',
        'ref_id',
        'ref_type',
        'quantity',
        'quantity_before',
        'quantity_after',
        'created_at',
        'created_by',
        'notes',
    ];

    /**
     * Reference types.
     */
    const RefType_InitialStock              = 'initial_stock';
    const RefType_ManualAdjustment          = 'manual_adjustment';
    const RefType_StockAdjustmentDetail     = 'stock_adjustment_detail';
    const RefType_SalesOrderDetail          = 'sales_order_detail';
    const RefType_SalesOrderReturnDetail    = 'sales_order_return_detail';
    const RefType_PurchaseOrderDetail       = 'purchase_order_detail';
    const RefType_PurchaseOrderReturnDetail = 'purchase_order_return_detail';

    const RefTypes = [
        self::RefType_InitialStock              => 'Stok Awal',
        self::RefType_ManualAdjustment          => 'Penyesuaian Manual',
        self::RefType_StockAdjustmentDetail     => 'Penyesuaian Stok',
        self::RefType_SalesOrderDetail          => 'Order Penjualan',
        self::RefType_SalesOrderReturnDetail    => 'Retur Penjualan',
        self::RefType_PurchaseOrderDetail       => 'Order Pembelian',
        self::RefType_PurchaseOrderReturnDetail => 'Retur Order Pembelian',
    ];

    protected $appends = [
        'formatted_id',
    ];

    protected function casts(): array
    {
        return [
            'product_id'       => 'integer',
            'product_name'     => 'string',
            'uom'              => 'string',
            'ref_id'           => 'integer',
            'ref_type'         => 'string',
            'quantity'         => 'decimal:3',
            'quantity_before'  => 'decimal:3',
            'quantity_after'   => 'decimal:3',
            'created_at'       => 'datetime',
            'created_by'       => 'integer',
            'updated_at'       => 'datetime',
            'updated_by'       => 'integer',
        ];
    }

    public function getFormattedIdAttribute()
    {
        return Setting::value('stock_movement_code_prefix', 'SM-')
            . Carbon::parse($this->created_at)->format('Ymd')
            . '-'
            . $this->id;
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public static function deleteByRef($ref_id, $ref_type)
    {
        return DB::delete(
            'DELETE FROM stock_movements WHERE ref_type = ? AND ref_id = ?',
            [StockMovement::RefType_PurchaseOrderDetail, $ref_id]
        );
    }
}
