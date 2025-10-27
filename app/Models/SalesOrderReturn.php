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

use App\Models\Traits\HasDocumentVersions;
use App\Models\Traits\HasTransactionCode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesOrderReturn extends BaseModel
{
    use HasDocumentVersions,
        HasFactory,
        HasTransactionCode,
        SoftDeletes;

    protected string $transactionPrefix = 'SOR';

    protected $fillable = [
        'code',
        'sales_order_id',
        'user_id', // Siapa yang memproses retur

        'customer_id',
        'customer_code',
        'customer_name',
        'customer_phone',
        'customer_address',

        'status',
        'refund_status',

        'datetime',

        'total_cost',
        'total_price',
        'total_discount',
        'total_tax',
        'grand_total',

        'total_refunded',
        'remaining_refund',

        'reason',
        'notes',
    ];

    protected $appends = [
        'status_label',
        'refund_status_label',

    ];

    // === Status Dokumen Retur ===
    public const Status_Draft    = 'draft';
    public const Status_Closed   = 'closed';
    public const Status_Canceled = 'canceled';

    public const Statuses = [
        self::Status_Draft    => 'Draft',
        self::Status_Closed   => 'Selesai',
        self::Status_Canceled => 'Dibatalkan',
    ];

    // === Status Refund (Pengembalian Dana) ===
    public const RefundStatus_Pending   = 'pending';
    public const RefundStatus_PartiallyRefunded = 'partially_refunded';
    public const RefundStatus_FullyRefunded   = 'fully_refunded';
    public const RefundStatus_NoRefund   = 'no_refund';

    public const RefundStatuses = [
        self::RefundStatus_Pending   => 'Menunggu Refund',
        self::RefundStatus_PartiallyRefunded => 'Refund Sebagian',
        self::RefundStatus_FullyRefunded   => 'Refund Lunas',
        self::RefundStatus_NoRefund   => 'Tidak ada Refund',
    ];


    protected function casts(): array
    {
        return [
            'code'              => 'string',
            'sales_order_id'    => 'integer',
            'user_id'           => 'integer',
            'customer_id'       => 'integer',
            'datetime'          => 'datetime',
            'status'            => 'string',
            'refund_status'     => 'string',

            // Kolom Keuangan
            'total_cost'        => 'decimal:2',
            'total_price'       => 'decimal:2',
            'total_discount'    => 'decimal:2',
            'total_tax'         => 'decimal:2',
            'grand_total'       => 'decimal:2',
            'total_refunded'    => 'decimal:2',
            'remaining_refund'  => 'decimal:2',

            'created_by' => 'integer',
            'updated_by' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    // === Accessors ===

    public function getStatusLabelAttribute(): string
    {
        return self::Statuses[$this->status] ?? '-';
    }

    public function getRefundStatusLabelAttribute(): string
    {
        return self::RefundStatuses[$this->refund_status] ?? '-';
    }

    // === Relasi ===

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(SalesOrderDetail::class, 'return_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SalesOrderPayment::class, 'return_id');
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(SalesOrderPayment::class, 'return_id');
    }

    public function updateGrandTotal(): void
    {
        $this->total_cost = $this->details()->sum('subtotal_cost');
        $this->total_price = $this->details()->sum('subtotal_price');
        $this->grand_total = $this->total_price + $this->total_tax - $this->total_discount;
    }

    public function updateBalanceAndStatus()
    {
        if ($this->salesOrder) {
            $this->total_refunded = abs(SalesOrderPayment::where('return_id', $this->id)
                ->sum('amount'));
            $this->remaining_refund = $this->salesOrder->balance;
        } else {
            $this->total_refunded = abs(SalesOrderPayment::where('return_id', $this->id)
                ->sum('amount'));
            $this->remaining_refund = $this->grand_total - $this->total_refunded;
        }

        $this->refund_status = SalesOrderReturn::RefundStatus_Pending;

        if ($this->total_refunded >= $this->grand_total) {
            $this->refund_status = SalesOrderReturn::RefundStatus_FullyRefunded;
        } else if ($this->total_refunded > 0) {
            $this->refund_status = SalesOrderReturn::RefundStatus_PartiallyRefunded;
        } else if ($this->remaining_refund <= 0) {
            $this->refund_status = SalesOrderReturn::RefundStatus_NoRefund;
        }
    }
}
