<?php

namespace App\Models;

class SalesOrderPayment extends BaseModel
{
    protected $fillable = [
        'order_id',
        'finance_account_id',
        'customer_id',
        'type',
        'amount',
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
