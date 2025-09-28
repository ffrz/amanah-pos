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

use Carbon\Carbon;
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

    protected $appends = ['total_income', 'total_expense', 'total_sales'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'opening_balance' => 'float',
        'closing_balance' => 'float',
        'total_income' => 'float',
        'total_expense' => 'float',
        'total_sales' => 'float',
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

    /**
     * Dapatkan total pemasukan untuk sesi kasir.
     *
     * @return float
     */
    public function getTotalIncomeAttribute(): float
    {
        $accountId = $this->cashierTerminal->financeAccount->id ?? null;

        if (!$accountId) {
            return 0;
        }

        return FinanceTransaction::where('account_id', $accountId)
            ->whereBetween('datetime', [$this->opened_at, $this->closed_at ?? Carbon::now()])
            ->where('amount', '>', 0)
            ->sum('amount');
    }

    /**
     * Dapatkan total pengeluaran untuk sesi kasir.
     *
     * @return float
     */
    public function getTotalExpenseAttribute(): float
    {
        $accountId = $this->cashierTerminal->financeAccount->id ?? null;

        if (!$accountId) {
            return 0;
        }

        $totalExpense = FinanceTransaction::where('account_id', $accountId)
            ->whereBetween('datetime', [$this->opened_at, $this->closed_at ?? Carbon::now()])
            ->where('amount', '<', 0)
            ->sum('amount');

        return abs($totalExpense);
    }

    /**
     * Dapatkan total penjualan untuk sesi kasir.
     *
     * @return float
     */
    public function getTotalSalesAttribute(): float
    {
        $total = SalesOrder::where('cashier_id', $this->user_id)
            ->where('status', 'closed')
            ->whereBetween('updated_at', [$this->opened_at, $this->closed_at ?? Carbon::now()])
            ->sum('grand_total');

        return $total;
    }
}
