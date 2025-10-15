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
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerWalletTransaction extends BaseModel
{
    use HasFactory,
        HasDocumentVersions,
        HasTransactionCode,
        SoftDeletes;

    protected string $transactionPrefix = 'CWTX';

    protected $fillable = [
        'customer_id',
        'finance_account_id',
        'code',
        'datetime',
        'type',
        'amount',
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
    const Type_Deposit = 'deposit';
    const Type_Refund = 'refund';
    const Type_SalesOrderPayment = 'sales_order_payment';
    const Type_SalesOrderRefund = 'sales_order_refund';
    const Type_Withdrawal = 'withdrawal';
    const Type_Adjustment = 'adjustment';

    const Types = [
        self::Type_Deposit => 'Top Up',
        self::Type_Refund => 'Refund',
        self::Type_SalesOrderPayment => 'Pembelian',
        self::Type_SalesOrderRefund => 'Refund Pembelian',
        self::Type_Withdrawal => 'Penarikan',
        self::Type_Adjustment => 'Penyesuaian',
    ];

    const RefType_SalesOrderPayment = 'sales_order_payment';
    const RefType_SalesOrderReturnRefund = 'sales_order_return_refund';
    const RefType_CustomerWalletTransactionConfirmation = 'customer_wallet_transaction_confirmation';

    const RefTypes = [
        self::RefType_SalesOrderPayment => 'Transaksi Pembayaran Penjualan',
        self::RefType_SalesOrderReturnRefund => ' Transaksi Refund Pembayaran Penjualan',
        self::RefType_CustomerWalletTransactionConfirmation => 'Konfirmasi Transaksi Wallet Pelanggan',
    ];

    // const RefTypeModels = [
    //     self::RefType_SalesOrderPayment => \App\Models\SalesOrderPayment::class,
    // ];

    protected function casts(): array
    {
        return [
            'customer_id' => 'integer',
            'finance_account_id' => 'integer',
            'datetime' => 'datetime',
            'type' => 'string',
            'amount' => 'decimal:2',
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

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function financeAccount()
    {
        return $this->belongsTo(FinanceAccount::class, 'finance_account_id');
    }
}
