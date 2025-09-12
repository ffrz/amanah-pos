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
        'has_wallet_access',
        'show_in_pos_payment',
        'show_in_purchasing_payment',
        'balance',
        'notes',
    ];

    /**
     * Account types.
     */
    const Type_Cash = 'cash';
    const Type_Bank = 'bank';
    const Type_PettyCash = 'petty_cash';
    const Type_CashierCash = 'cashier_cash';

    const Types = [
        self::Type_CashierCash => 'Kas Kasir',
        self::Type_PettyCash => 'Kasir Tunai Kecil',
        self::Type_Cash => 'Kas Tunai Besar',
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
            'has_wallet_access' => 'boolean',
            'show_in_pos_payment' => 'boolean',
            'show_in_purchasing_payment' => 'boolean',
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
