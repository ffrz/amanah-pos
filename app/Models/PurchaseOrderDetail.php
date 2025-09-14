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
            'quantity'      => 'decimal:3',
            'uom'           => 'string',
            'cost'          => 'decimal:2',
            'subtotal_cost' => 'decimal:2',
            'notes'         => 'string',
        ];
    }
}
