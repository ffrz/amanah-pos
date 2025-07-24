<?php

namespace App\Models;

class StockAdjustmentDetail extends Model
{
    protected $fillable = [
        'parent_id',
        'product_id',
        'product_name',
        'old_quantity',
        'new_quantity',
        'balance',
        'uom',
        'cost',
        'price',
        'subtotal_cost',
        'subtotal_price',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'parent_id'       => 'integer',
            'product_id'      => 'integer',
            'product_name'    => 'string',
            'old_quantity'    => 'decimal:3',
            'new_quantity'    => 'decimal:3',
            'balance'         => 'decimal:3',
            'uom'             => 'string',
            'cost'            => 'decimal:2',
            'price'           => 'decimal:2',
            'subtotal_cost'   => 'decimal:2',
            'subtotal_price'  => 'decimal:2',
            'notes'           => 'string',
        ];
    }


    public function parent()
    {
        return $this->belongsTo(StockAdjustment::class, 'parent_id');
    }
}
