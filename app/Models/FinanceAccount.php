<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class FinanceAccount extends BaseModel
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

    protected function casts(): array
    {
        return [
            'name' => 'string',
            'type' => 'string',
            'bank' => 'string',
            'number' => 'string',
            'holder' => 'string',
            'active' => 'boolean',
            'balance' => 'decimal:2',
            'notes' => 'string',
            'created_by' => 'integer',
            'updated_by' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',

        ];
    }

    public static function totalActiveBalance()
    {
        return DB::select(
            'select sum(balance) as sum from finance_accounts where active=1'
        )[0]->sum;
    }
}
