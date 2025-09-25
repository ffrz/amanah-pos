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

class PurchaseOrderDetail extends BaseModel
{
    protected $fillable = [
        'parent_id',
        'product_id',
        'product_name',
        'product_uom',
        'quantity',
        'cost',
        'subtotal_cost',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'parent_id'     => 'integer',
            'product_id'    => 'integer',
            'product_name'  => 'string',
            'quantity'      => 'decimal:3',
            'product_uom'   => 'string',
            'cost'          => 'decimal:2',
            'subtotal_cost' => 'decimal:2',
            'notes'         => 'string',
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

    protected function getSubtotalCostAttribute(string $value): float
    {
        return (float) $value;
    }

    public function parent()
    {
        return $this->belongsTo(PurchaseOrder::class, 'parent_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
