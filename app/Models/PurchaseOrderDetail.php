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
}
