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

class SupplierLedger extends BaseModel
{
    use HasFactory,
        HasTransactionCode,
        SoftDeletes;

    protected string $transactionPrefix = 'SLTX'; // Supplier Ledger Transaction

    protected $fillable = [
        'supplier_id',
        'finance_account_id',
        'code',
        'datetime',
        'type',             // bill, payment, return, adjustment
        'amount',           // (+) Menambah Utang Kita, (-) Mengurangi Utang Kita
        'running_balance',  // Saldo setelah transaksi ini
        'image_path',
        'ref_type',
        'ref_id',
        'notes',
    ];

    protected $appends = [
        'type_label',
    ];

    /**
     * Transaction types.
     */
    const Type_OpeningBalance = 'opening_balance';
    const Type_Bill = 'bill';       // Purchase Order Final / Tagihan Masuk
    const Type_Payment = 'payment'; // Purchase Order Payment / Kita Bayar
    const Type_Return = 'return';   // Purchase Order Return / Kita Retur
    const Type_Adjustment = 'adjustment';

    const Types = [
        self::Type_OpeningBalance => 'Saldo Awal',
        self::Type_Bill => 'Tagihan Pembelian',
        self::Type_Payment => 'Pembayaran',
        self::Type_Return => 'Retur Pembelian',
        self::Type_Adjustment => 'Penyesuaian',
    ];

    /**
     * Reference Types.
     */
    const RefType_PurchaseOrder = 'purchase_order';
    const RefType_PurchaseOrderPayment = 'purchase_order_payment';
    const RefType_PurchaseOrderReturn = 'purchase_order_return';

    const RefTypes = [
        self::RefType_PurchaseOrder => 'Order Pembelian',
        self::RefType_PurchaseOrderPayment => 'Pembayaran Pembelian',
        self::RefType_PurchaseOrderReturn => 'Retur Pembelian',
    ];

    protected function casts(): array
    {
        return [
            'supplier_id' => 'integer',
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
     * Relasi ke Supplier.
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    /**
     * Relasi Polimorfik ke sumber transaksi.
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
