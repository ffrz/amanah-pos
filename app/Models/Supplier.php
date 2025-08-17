<?php

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
