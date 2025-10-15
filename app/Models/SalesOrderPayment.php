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

use App\Models\Traits\HasTransactionCode;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesOrderPayment extends BaseModel
{
    use SoftDeletes, HasTransactionCode;

    protected string $transactionPrefix = 'SOTX';

    protected $fillable = [
        'code',
        'order_id',
        'finance_account_id',
        'customer_id',
        'type',
        'amount',
    ];

    protected $appends = [

        'type_label',
    ];

    /**
     * Transaction types.
     */
    const Type_Transfer = 'transfer';
    const Type_Cash = 'cash';
    const Type_Wallet = 'wallet';

    const Types = [
        self::Type_Transfer => 'Transfer',
        self::Type_Cash => 'Tunai',
        self::Type_Wallet => 'Wallet',
    ];

    protected function casts(): array
    {
        return [
            'order_id'    => 'integer',
            'finance_account_id' => 'integer',
            'customer_id' => 'integer',
            'type'        => 'string',
            'amount'      => 'decimal:3',
            'created_at'  => 'datetime',
        ];
    }

    public function getTypeLabelAttribute()
    {
        return self::Types[$this->type] ?? '-';
    }

    /**
     * @return SalesOrder
     */
    public function order()
    {
        return $this->belongsTo(SalesOrder::class, 'order_id');
    }

    public function account()
    {
        return $this->belongsTo(FinanceAccount::class, 'finance_account_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}
