<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class FinanceAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'bank',
        'number',
        'holder',
        'active',
        'balance',
        'notes'
    ];

    /**
     * Account types.
     */
    const Type_Cash = 'cash';
    const Type_Bank = 'bank';

    const Types = [
        self::Type_Cash => 'Kas / Tunai',
        self::Type_Bank => 'Rekening Bank',
    ];

    public static function totalActiveBalance()
    {
        return DB::select(
            'select sum(balance) as sum from finance_accounts where active=1'
        )[0]->sum;
    }
}
