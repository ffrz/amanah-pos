<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class FinanceTransaction extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'datetime',
        'type',
        'amount',
        'ref_id',
        'ref_type',
        'notes',
    ];

    protected $appends = [
        'ref_type_label',
        'type_label',
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

    const RefTypes = [
        self::RefType_FinanceTransaction => 'Transaksi Keuangan',
        self::RefType_CustomerWalletTransaction => 'Transaksi Dompet Santri',
    ];

    const RefTypeModels = [
        self::RefType_FinanceTransaction => \App\Models\FinanceTransaction::class,
        self::RefType_CustomerWalletTransaction => \App\Models\CustomerWalletTransaction::class,
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
        ];
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
                return 'Transaksi Dompet Santri';
            case \App\Models\OperationalCost::class:
                return 'Biaya Operasional';
            default:
                return 'Lainnya';
        }
    }
}
