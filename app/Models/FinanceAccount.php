<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class FinanceAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'bank',
        'number',
        'holder',
        'active',
        'balance',
        'notes'
    ];

    /**
     * Account types.
     */
    const Type_Cash = 'cash';
    const Type_Bank = 'bank';

    const Types = [
        self::Type_Cash => 'Kas / Tunai',
        self::Type_Bank => 'Rekening Bank',
    ];

    protected function casts(): array
    {
        return [
            'name' => 'string',
            'type' => 'string',
            'bank' => 'string',
            'number' => 'string',
            'holder' => 'string',
            'active' => 'boolean',
            'balance' => 'decimal:2',
            'notes' => 'string',
            'created_by_uid' => 'integer',
            'updated_by_uid' => 'integer',
            'created_datetime' => 'datetime',
            'updated_datetime' => 'datetime',

        ];
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_uid');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by_uid');
    }

    public static function totalActiveBalance()
    {
        return DB::select(
            'select sum(balance) as sum from finance_accounts where active=1'
        )[0]->sum;
    }
}
