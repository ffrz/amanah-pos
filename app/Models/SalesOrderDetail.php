<?php

namespace App\Models;

class SalesOrderDetail extends Model
{
    protected $fillable = [
        'parent_id',
        'product_id',
        'product_name',
        'quantity',
        'uom',
        'cost',
        'subtotal_cost',
        'price',
        'subtotal_price',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'parent_id'       => 'integer',
            'product_id'      => 'integer',
            'product_name'    => 'string',
            'quantity'        => 'decimal:2',
            'uom'             => 'string',
            'cost'            => 'decimal:2',
            'subtotal_cost'   => 'decimal:2',
            'price'           => 'decimal:2',
            'subtotal_price'  => 'decimal:2',
            'notes'           => 'string',
        ];
    }


    public function parent()
    {
        return $this->belongsTo(SalesOrder::class, 'parent_id');
    }
}
