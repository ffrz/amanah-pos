<?php

namespace App\Models;

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

    const RefTypes = [
        self::RefType_InitialStock     => 'Stok Awal',
        self::RefType_ManualAdjustment => 'Penyesuaian Manual',
        self::RefType_StockAdjustment  => 'Penyesuaian Stok'
    ];

    protected function casts(): array
    {
        return [
            'product_id'        => 'integer',
            'ref_id'            => 'integer',
            'ref_type'          => 'string',
            'quantity'          => 'decimal:3',
            'created_at'  => 'datetime',
            'created_by'    => 'integer',
        ];
    }

    // hasil penyederhanaan, ini jadi tidak diperlukan
    // Define the ref relationship dynamically based on ref_type and ref_id
    // public function ref()
    // {
    //     // Check ref_type and return the corresponding related model
    //     switch ($this->ref_type) {
    //         case 'sales_order':
    //             return $this->belongsTo(SalesOrder::class, 'ref_id');
    //         case 'sales_order_return':
    //             return $this->belongsTo(SalesOrderReturn::class, 'ref_id');
    //         case 'purchase_order':
    //             return $this->belongsTo(PurchaseOrder::class, 'ref_id');
    //         case 'purchase_order_return':
    //             return $this->belongsTo(PurchaseOrderReturn::class, 'ref_id');
    //         case 'stock_adjustment':
    //             return $this->belongsTo(StockAdjustment::class, 'ref_id');
    //         default:
    //             return null;
    //     }
    // }

    // Define the ref relationship dynamically based on ref_type and ref_id
    public function ref()
    {
        // Check ref_type and return the corresponding related model
        switch ($this->ref_detail_type) {
            case 'sales_order_detail':
                return $this->belongsTo(SalesOrderDetail::class, 'ref_detail_id');
                // case 'sales_order_return_detail':
                //     return $this->belongsTo(SalesOrderReturnDetail::class, 'ref_detail_id');
            case 'purchase_order_detail':
                return $this->belongsTo(PurchaseOrderDetail::class, 'ref_detail_id');
                // case 'purchase_order_return_detail':
                //     return $this->belongsTo(PurchaseOrderReturnDetail::class, 'ref_detail_id');
            case 'stock_adjustment_detail':
                return $this->belongsTo(StockAdjustmentDetail::class, 'ref_detail_id');
            default:
                return null;
        }
    }
}
