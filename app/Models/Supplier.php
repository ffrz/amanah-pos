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

use App\Models\Traits\HasDocumentVersions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class Supplier extends BaseModel
{
    use HasFactory,
        HasDocumentVersions;

    protected $fillable = [
        'name',

        'phone_1',
        'phone_2',
        'phone_3',

        'address',
        'return_address',

        'bank_account_name_1',
        'bank_account_number_1',
        'bank_account_holder_1',

        'bank_account_name_2',
        'bank_account_number_2',
        'bank_account_holder_2',

        'url_1',
        'url_2',

        'active',
        'balance',

        'notes',
    ];


    protected $appends = [
        'actual_balance',
    ];

    protected function casts(): array
    {
        return [
            'name'    => 'string',
            'phone_1'   => 'string',
            'phone_2'   => 'string',
            'phone_3'   => 'string',
            'address' => 'string',
            'active'  => 'boolean',
            'balance' => 'decimal:2',
            'actual_balance' => 'decimal:2',
            'bank_account_number' => 'string',
            'return_address' => 'string',
            'created_by'     => 'integer',
            'updated_by'     => 'integer',
            'created_at'     => 'datetime',
            'updated_at'     => 'datetime',
        ];
    }

    public static function activeSupplierCount()
    {
        return DB::select(
            'select count(0) as count from suppliers where active = 1'
        )[0]->count;
    }

    public function getActualBalanceAttribute()
    {
        return -DB::table('purchase_orders')
            ->where('supplier_id', $this->id)
            ->where('status', 'closed')
            ->sum(DB::raw('grand_total - total_paid'));
    }
}
