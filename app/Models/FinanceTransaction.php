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
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinanceTransaction extends BaseModel
{
    use HasFactory,
        HasDocumentVersions,
        SoftDeletes;

    protected $fillable = [
        'account_id',
        'datetime',
        'type',
        'amount',
        'ref_id',
        'ref_type',
        'notes',
        'image_path',
    ];

    protected $appends = [
        'ref_type_label',
        'type_label',
        'formatted_id',
    ];

    /**
     * Transaction types.
     */
    const Type_Income = 'income';
    const Type_Expense = 'expense';
    const Type_Transfer = 'transfer';
    const Type_Adjustment = 'adjustment';

    const Types = [
        self::Type_Income => 'Pemasukan',
        self::Type_Expense => 'Pengeluaran',
        self::Type_Transfer => 'Transfer',
        self::Type_Adjustment => 'Penyesuaian',
    ];

    const RefType_FinanceTransaction = 'finance_transaction';
    const RefType_CustomerWalletTransaction = 'customer_wallet_transaction';
    const RefType_SalesOrderPayment = 'sales_order_payment';
    const RefType_SalesOrderReturnRefund = 'sales_order_return_refund';
    const RefType_PurchaseOrderPayment = 'sales_order_payment';
    const RefType_PurchaseOrderReturnRefund = 'sales_order_return_refund';
    const RefType_OperationalCost = 'operational_cost';

    const RefTypes = [
        self::RefType_FinanceTransaction => 'Transaksi Keuangan',
        self::RefType_CustomerWalletTransaction => 'Transaksi Dompet Pelanggan',
        self::RefType_SalesOrderPayment => 'Transaksi Pembayaran Penjualan',
        self::RefType_SalesOrderReturnRefund => 'Transaksi Refund Pembayaran Penjualan',
        self::RefType_PurchaseOrderPayment => 'Transaksi Pembayaran Pembelian',
        self::RefType_PurchaseOrderReturnRefund => 'Transaksi Refund Pembayaran Pembelian',
        self::RefType_OperationalCost => 'Transaksi Biaya Operasional',
    ];

    const RefTypeModels = [
        self::RefType_FinanceTransaction => \App\Models\FinanceTransaction::class,
        self::RefType_CustomerWalletTransaction => \App\Models\CustomerWalletTransaction::class,
        self::RefType_SalesOrderPayment => \App\Models\SalesOrderPayment::class,
        self::RefType_PurchaseOrderPayment => \App\Models\PurchaseOrderPayment::class,
        self::RefType_OperationalCost => \App\Models\OperationalCost::class,
    ];

    protected function casts(): array
    {
        return [
            'account_id' => 'integer',
            'datetime' => 'datetime',
            'type' => 'string',
            'amount' => 'decimal:2',
            'ref_id' => 'integer',
            'ref_type' => 'string',
            'notes' => 'string',
            'created_by' => 'integer',
            'updated_by' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'image_path' => 'string'
        ];
    }

    public function getFormattedIdAttribute()
    {
        return Setting::value('finance_transaction_code_prefix', 'FTX-')
            . Carbon::parse($this->created_at)->format('Ymd')
            . '-'
            . $this->id;
    }

    public function getTypeLabelAttribute()
    {
        return self::Types[$this->type] ?? '-';
    }

    public function getRefTypeLabelAttribute()
    {
        return self::RefTypes[$this->ref_type] ?? '-';
    }

    public function getRefModelAttribute()
    {
        $modelClass = self::RefTypeModels[$this->ref_type] ?? null;
        if ($modelClass && $this->ref_id) {
            return $modelClass::find($this->ref_id);
        }
        return null;
    }

    public function account()
    {
        return $this->belongsTo(FinanceAccount::class, 'account_id');
    }

    public function ref()
    {
        return $this->morphTo();
    }

    public static function refTypeLabel($model): string
    {
        switch (get_class($model)) {
            case \App\Models\CustomerWalletTransaction::class:
                return 'Transaksi Dompet Pelanggan';
            case \App\Models\OperationalCost::class:
                return 'Biaya Operasional';
            default:
                return 'Lainnya';
        }
    }

    public function cashierSessions(): BelongsToMany
    {
        return $this->belongsToMany(CashierSession::class, 'cashier_session_transactions');
    }

    public static function deleteByRef($id, $type)
    {
        static::where('ref_id', $id)->where('ref_type', $type)->delete();
    }
}
