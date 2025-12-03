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

use App\Models\Traits\HasDocumentVersions;
use App\Models\Traits\HasTransactionCode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerLedger extends BaseModel
{
    use HasFactory,
        HasTransactionCode,
        SoftDeletes;

    protected string $transactionPrefix = 'CLTX'; // Customer Ledger Transaction

    protected $fillable = [
        'customer_id',
        'finance_account_id',
        'code',
        'datetime',
        'type',             // invoice, payment, return, adjustment
        'amount',           // (+) Menambah Piutang, (-) Mengurangi Piutang
        'running_balance',  // Saldo setelah transaksi ini
        'image_path',
        'ref_type',         // Polymorphic Reference Type
        'ref_id',           // Polymorphic Reference ID
        'notes',
    ];

    protected $appends = [
        'type_label',
    ];

    /**
     * Transaction types (Jenis Mutasi).
     */
    const Type_OpeningBalance = 'opening_balance';
    const Type_Invoice = 'invoice'; // Sales Order Final
    const Type_Payment = 'payment'; // Sales Order Payment
    const Type_Return = 'return';   // Sales Order Return
    const Type_Adjustment = 'adjustment'; // Koreksi Manual

    const Types = [
        self::Type_OpeningBalance => 'Saldo Awal',
        self::Type_Invoice => 'Tagihan Penjualan',
        self::Type_Payment => 'Pembayaran',
        self::Type_Return => 'Retur Penjualan',
        self::Type_Adjustment => 'Penyesuaian',
    ];

    /**
     * Reference Types (Model Sumber).
     * Pastikan string ini sesuai dengan Morph Map di AppServiceProvider jika ada,
     * atau nama full class jika tidak pakai morph map.
     */
    const RefType_SalesOrder = 'sales_order';
    const RefType_SalesOrderPayment = 'sales_order_payment';
    const RefType_SalesOrderReturn = 'sales_order_return';

    const RefTypes = [
        self::RefType_SalesOrder => 'Order Penjualan',
        self::RefType_SalesOrderPayment => 'Pembayaran Penjualan',
        self::RefType_SalesOrderReturn => 'Retur Penjualan',
    ];

    protected function casts(): array
    {
        return [
            'customer_id' => 'integer',
            'finance_account_id' => 'integer',
            'datetime' => 'datetime',
            'type' => 'string',
            'amount' => 'decimal:2',
            'running_balance' => 'decimal:2',
            'ref_type' => 'string',
            'ref_id' => 'integer',
            'notes' => 'string',
            'created_by' => 'integer',
            'updated_by' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'image_path' => 'string'
        ];
    }

    public function getTypeLabelAttribute()
    {
        return self::Types[$this->type] ?? '-';
    }

    /**
     * Relasi ke Customer.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * Relasi Polimorfik ke sumber transaksi (SalesOrder, Payment, dll).
     */
    public function ref()
    {
        return $this->morphTo();
    }

    public function financeAccount(): BelongsTo
    {
        return $this->belongsTo(FinanceAccount::class, 'finance_account_id');
    }
}
