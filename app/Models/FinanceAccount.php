<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

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

    public static function getDefaultCashAccount(): ?self
    {
        return self::where('is_default_cash', true)->first();
    }

    public static function getDefaultWalletAccount(): ?self
    {
        return self::where('is_default_wallet', true)->first();
    }
}
