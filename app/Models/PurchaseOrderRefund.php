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
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrderRefund extends BaseModel
{
    use SoftDeletes;

    protected $fillable = [
        'purchase_order_return_id', // Merujuk ke dokumen retur pembelian
        'supplier_id',
        'finance_account_id',
        'type',
        'amount',
        'notes', // Catatan
    ];

    protected $appends = [
        'formatted_id',
        'type_label',
    ];

    /**
     * Transaction types (misalnya: Supplier mengirim uang/Credit Note).
     */
    const Type_Transfer = 'transfer';
    const Type_Cash     = 'cash';
    const Type_CreditNote = 'credit_note'; // Paling umum dalam pembelian

    const Types = [
        self::Type_Transfer => 'Transfer',
        self::Type_Cash     => 'Tunai',
        self::Type_CreditNote => 'Credit Note',
    ];

    protected function casts(): array
    {
        return [
            'purchase_order_return_id' => 'integer',
            'supplier_id'              => 'integer',
            'finance_account_id'       => 'integer',
            'type'                     => 'string',
            'amount'                   => 'decimal:2',
            'notes'                    => 'string',
            'created_at'               => 'datetime',
        ];
    }

    public function getFormattedIdAttribute(): string
    {
        // Prefix untuk Purchase Order Refund / Credit
        // return Setting::value('purchase_refund_code_prefix', 'PORF-')
        return 'PORF-'
            . Carbon::parse($this->created_at)->format('Ymd')
            . '-'
            . $this->id;
    }


    public function getTypeLabelAttribute(): string
    {
        return self::Types[$this->type] ?? '-';
    }

    // === Relasi ===

    public function purchaseOrderReturn(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderReturn::class, 'purchase_order_return_id');
    }

    public function account(): BelongsTo
    {
        // Akun di mana dana diterima (misalnya Bank atau Kas Perusahaan)
        return $this->belongsTo(FinanceAccount::class, 'finance_account_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }
}
