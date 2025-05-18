<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class CustomerWalletTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'finance_account_id',
        'datetime',
        'type',
        'amount',
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
    const Type_Purchase = 'purchase';
    const Type_Withdrawal = 'withdrawal';
    const Type_Adjustment = 'adjustment';

    const Types = [
        self::Type_Deposit => 'Deposit',
        self::Type_Refund => 'Refund',
        self::Type_Purchase => 'Pembelian',
        self::Type_Withdrawal => 'Penarikan',
        self::Type_Adjustment => 'Penyesuaian',
    ];

    public function getTypeLabelAttribute()
    {
        return self::Types[$this->type] ?? '-';
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_uid');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by_uid');
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
