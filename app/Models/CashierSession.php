<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CashierSession extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cashier_sessions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'cashier_terminal_id',
        'opening_balance',
        'closing_balance',
        'is_closed',
        'opened_at',
        'closed_at',
        'opening_notes',
        'closing_notes',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'opening_balance' => 'float',
        'closing_balance' => 'float',
        'is_closed' => 'boolean',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that started the session.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the cash register for the session.
     */
    public function cashierTerminal(): BelongsTo
    {
        return $this->belongsTo(CashierTerminal::class);
    }

    /**
     * Get the finance transactions for the session.
     */
    public function financeTransactions(): BelongsToMany
    {
        return $this->belongsToMany(FinanceTransaction::class, 'cashier_session_transactions');
    }
}
