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

use Carbon\Carbon;

class PurchaseOrderPayment extends BaseModel
{
    protected $fillable = [
        'order_id',
        'finance_account_id',
        'supplier_id',
        'type',
        'amount',
    ];

    protected $appends = [
        'formatted_id',
        'type_label',
    ];

    const Type_Transfer = 'transfer';
    const Type_Cash = 'cash';

    const Types = [
        self::Type_Transfer => 'Akun Kas',
        self::Type_Cash => 'Tunai',
    ];

    protected function casts(): array
    {
        return [
            'order_id'    => 'integer',
            'finance_account_id' => 'integer',
            'supplier_id' => 'integer',
            'type'        => 'string',
            'amount'      => 'decimal:3',
            'created_at'  => 'datetime',
        ];
    }

    public function getFormattedIdAttribute()
    {
        return Setting::value('purchase_order_payment_code_prefix', 'POPY-')
            . Carbon::parse($this->created_at)->format('Ymd')
            . '-'
            . $this->id;
    }


    public function getTypeLabelAttribute()
    {
        return self::Types[$this->type] ?? '-';
    }

    public function order()
    {
        return $this->belongsTo(PurchaseOrder::class, 'order_id');
    }

    public function account()
    {
        return $this->belongsTo(FinanceAccount::class, 'finance_account_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }
}
