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

    public function transactions()
    {
        return $this->hasMany(FinanceTransaction::class, 'account_id');
    }

    public function isUsedInTransaction(): bool
    {
        return $this->transactions()->exists();
    }

    public static function totalActiveBalance()
    {
        return DB::select(
            'select sum(balance) as sum from finance_accounts where active=1'
        )[0]->sum;
    }

    public function cashRegister()
    {
        return $this->hasOne(CashRegister::class, 'finance_account_id');
    }
}
