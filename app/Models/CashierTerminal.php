<?php

namespace App\Models;

use App\Models\Traits\HasDocumentVersions;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CashierTerminal extends BaseModel
{
    use HasDocumentVersions;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cashier_terminals';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'location',
        'notes',
        'finance_account_id',
        'active',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'finance_account_id' => 'integer',
        'active'     => 'boolean',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the finance account associated with the cash register.
     */
    public function financeAccount(): BelongsTo
    {
        return $this->belongsTo(FinanceAccount::class, 'finance_account_id');
    }

    public function activeSession(): HasOne
    {
        return $this->hasOne(CashierSession::class)->where('is_closed', false);
    }
}
