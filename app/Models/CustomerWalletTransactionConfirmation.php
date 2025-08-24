<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class CustomerWalletTransactionConfirmation extends BaseModel
{
    use HasFactory;

    protected $table = 'customer_wallet_trx_confirmations';

    protected $fillable = [
        'customer_id',
        'finance_account_id',
        'datetime',
        'amount',
        'image_path',
        'status',
        'notes',
    ];

    const Status_Pending = 'pending';
    const Status_Approved = 'approved';
    const Status_Rejected = 'rejected';
    const Status_Canceled = 'canceled';

    const Statuses = [
        self::Status_Pending  => 'Menunggu',
        self::Status_Approved => 'Disetujui',
        self::Status_Rejected => 'Ditolak',
        self::Status_Canceled => 'Dibatalkan',
    ];

    protected function casts(): array
    {
        return [
            'customer_id' => 'integer',
            'finance_account_id' => 'integer',
            'datetime' => 'datetime',
            'type' => 'string',
            'status' => 'string',
            'amount' => 'decimal:2',
            'created_by' => 'integer',
            'updated_by' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
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
