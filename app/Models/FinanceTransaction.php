<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class FinanceTransaction extends Model
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


    public function account()
    {
        return $this->belongsTo(FinanceAccount::class, 'account_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_uid');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by_uid');
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
