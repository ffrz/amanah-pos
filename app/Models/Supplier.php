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

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class Supplier extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'address',
        'active',
        'bank_account_number',
        'return_address'
    ];

    protected function casts(): array
    {
        return [
            'name'                 => 'string',
            'phone'                => 'string',
            'address'              => 'string',
            'active'               => 'boolean',
            'bank_account_number'  => 'string',
            'return_address'       => 'string',
            'created_by' => 'integer',
            'updated_by' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public static function activeSupplierCount()
    {
        return DB::select(
            'select count(0) as count from suppliers where active = 1'
        )[0]->count;
    }
}
