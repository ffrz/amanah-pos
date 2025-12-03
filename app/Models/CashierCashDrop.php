<?php

/**
 * Proprietary Software / Perangkat Lunak Proprietary
 * Copyright (c) 2025 Fahmi Fauzi Rahman. All rights reserved.
 * * EN: Unauthorized use, copying, modification, or distribution is prohibited.
 * ID: Penggunaan, penyalinan, modifikasi, atau distribusi tanpa izin dilarang.
 * * See the LICENSE file in the project root for full license information.
 * Lihat file LICENSE di root proyek untuk informasi lisensi lengkap.
 * * GitHub: https://github.com/ffrz
 * Email: fahmifauzirahman@gmail.com
 */

namespace App\Models;

use App\Models\Traits\HasDocumentVersions;
use App\Models\Traits\HasTransactionCode;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CashierCashDrop extends BaseModel
{
    use HasDocumentVersions,
        HasTransactionCode,
        SoftDeletes;

    protected string $transactionPrefix = 'CCD';

    const Status_Pending = 'pending';
    const Status_Approved = 'approved';
    const Status_Rejected = 'rejected';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cashier_cash_drops';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'datetime',
        'cashier_id',
        'terminal_id',
        'cashier_session_id',
        'source_finance_account_id',
        'target_finance_account_id',
        'amount',
        'status',
        'approved_by',
        'approved_at',
        'notes',
        'image_path',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'datetime' => 'datetime',
        'amount' => 'float',
        'approved_at' => 'datetime',
        'cashier_id' => 'integer',
        'terminal_id' => 'integer',
        'cashier_session_id' => 'integer',
        'source_finance_account_id' => 'integer',
        'target_finance_account_id' => 'integer',
        'approved_by' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the cashier (user) who made the drop.
     */
    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    /**
     * Get the terminal used for the drop.
     */
    public function cashierTerminal(): BelongsTo
    {
        return $this->belongsTo(CashierTerminal::class, 'terminal_id');
    }

    /**
     * Get the cashier session associated with the drop.
     */
    public function cashierSession(): BelongsTo
    {
        return $this->belongsTo(CashierSession::class, 'cashier_session_id');
    }

    /**
     * Get the source finance account (Cashier Drawer).
     */
    public function sourceFinanceAccount(): BelongsTo
    {
        return $this->belongsTo(FinanceAccount::class, 'source_finance_account_id');
    }

    /**
     * Get the target finance account (Safe/Bank).
     */
    public function targetFinanceAccount(): BelongsTo
    {
        return $this->belongsTo(FinanceAccount::class, 'target_finance_account_id');
    }

    /**
     * Get the user who approved the drop.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Helper to check if status is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === self::Status_Approved;
    }
}
