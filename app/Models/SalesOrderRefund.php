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
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesOrderRefund extends BaseModel
{
    use SoftDeletes, HasTransactionCode;

    protected string $transactionPrefix = 'SORF';

    protected $fillable = [
        'code',
        'sales_order_return_id', // Merujuk ke dokumen retur penjualan
        'finance_account_id',
        'customer_id',
        'type',
        'amount',
        'notes', // Tambahkan notes dari skema migrasi
    ];

    protected $appends = [

        'type_label',
    ];

    /**
     * Transaction types (dapat sama dengan payment).
     */
    const Type_Transfer = 'transfer';
    const Type_Cash     = 'cash';
    const Type_Wallet   = 'wallet';

    const Types = [
        self::Type_Transfer => 'Transfer',
        self::Type_Cash     => 'Tunai',
        self::Type_Wallet   => 'Wallet',
    ];

    protected function casts(): array
    {
        return [
            'sales_order_return_id' => 'integer',
            'finance_account_id'    => 'integer',
            'customer_id'           => 'integer',
            'type'                  => 'string',
            'amount'                => 'decimal:2',
            'notes'                 => 'string',
            'created_at'            => 'datetime',
        ];
    }

    public function getTypeLabelAttribute(): string
    {
        return self::Types[$this->type] ?? '-';
    }

    // === Relasi ===

    public function salesOrderReturn(): BelongsTo
    {
        return $this->belongsTo(SalesOrderReturn::class, 'sales_order_return_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(FinanceAccount::class, 'finance_account_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}
