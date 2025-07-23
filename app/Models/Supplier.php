<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'phone', 'address', 'active', 'bank_account_number', 'return_address'
    ];

    public static function activeSupplierCount()
    {
        return DB::select(
            'select count(0) as count from suppliers where active = 1'
        )[0]->count;
    }
}
