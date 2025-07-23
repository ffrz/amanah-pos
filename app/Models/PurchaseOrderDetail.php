<?php

namespace App\Models;

class PurchaseOrderDetail extends Model
{
    protected $fillable = [
        'parent_id',
        'product_id',
        'product_name',
        'quantity',
        'uom',
        'cost',
        'subtotal_cost',
        'notes',
    ];

    public function parent()
    {
        return $this->belongsTo(PurchaseOrder::class, 'parent_id');
    }

    protected function casts(): array
    {
        return [
            'parent_id'     => 'integer',
            'product_id'    => 'integer',
            'product_name'  => 'string',
            'quantity'      => 'decimal:2',
            'uom'           => 'string',
            'cost'          => 'decimal:2',
            'subtotal_cost' => 'decimal:2',
            'notes'         => 'string',
        ];
    }
}
