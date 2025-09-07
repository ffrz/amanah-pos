<?php

namespace App\Models;

class SalesOrderDetail extends BaseModel
{
    protected $fillable = [
        'parent_id',
        'product_id',
        'product_name',
        'product_barcode',
        'product_uom',
        'quantity',
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
            'product_uom'     => 'string',
            'product_barcode' => 'string',
            'quantity'        => 'decimal:3',
            'cost'            => 'decimal:2',
            'price'           => 'decimal:2',
            'subtotal_cost'   => 'decimal:2',
            'subtotal_price'  => 'decimal:2',
            'notes'           => 'string',
        ];
    }

    public function parent()
    {
        return $this->belongsTo(SalesOrder::class, 'parent_id');
    }
}
