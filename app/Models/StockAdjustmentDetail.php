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

use Illuminate\Database\Eloquent\SoftDeletes;

class StockAdjustmentDetail extends BaseModel
{
    use SoftDeletes;

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
