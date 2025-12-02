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

use App\Models\Traits\HasTransactionCode;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

/**
 * StockMovement Model
 */
class StockMovement extends BaseModel
{
    use SoftDeletes,
        HasTransactionCode;

    protected string $transactionPrefix = 'SM';

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

        'parent_id',
        'parent_ref_type',
        'party_id',
        'party_code',
        'party_name',
        'party_type',
        'document_code',
        'document_datetime',

    ];

    /**
     * Parent Reference types.
     */
    const ParentRefType_SalesOrder          = 'sales_order';
    const ParentRefType_SalesOrderReturn    = 'sales_order_return';
    const ParentRefType_PurchaseOrder       = 'purchase_order';
    const ParentRefType_PurchaseOrderReturn = 'purchase_order_return';
    const ParentRefType_StockAdjustment     = 'stock_adjustment';

    const ParentRefTypes = [
        self::ParentRefType_StockAdjustment     => 'Penyesuaian Manual',
        self::ParentRefType_SalesOrder          => 'Penjualan',
        self::ParentRefType_SalesOrderReturn    => 'Retur Penjualan',
        self::ParentRefType_PurchaseOrder       => 'Pembelian',
        self::ParentRefType_StockAdjustment     => 'Retur Pembelian',
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
        self::RefType_SalesOrderDetail          => 'Penjualan',
        self::RefType_SalesOrderReturnDetail    => 'Retur Penjualan',
        self::RefType_PurchaseOrderDetail       => 'Pembelian',
        self::RefType_PurchaseOrderReturnDetail => 'Retur Pembelian',
    ];

    protected $appends = [];

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
            'parent_id'        => 'integer',
            'party_id'         => 'integer',
            'party_code'       => 'string',
            'party_name'       => 'string',
            'party_type'       => 'string',
            'parent_ref_type'  => 'string',
            'document_code'    => 'string',
            'document_datetime' => 'datetime',
        ];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
