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
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrderReturn extends BaseModel
{
    use HasDocumentVersions,
        HasFactory,
        HasTransactionCode,
        SoftDeletes;

    protected string $transactionPrefix = 'POR';

    protected $fillable = [
        'purchase_order_id',
        'user_id', // Siapa yang memproses retur
        'code',
        'supplier_id',
        'supplier_code',
        'supplier_name',
        'supplier_phone',
        'supplier_address',

        'status',
        'refund_status',

        'datetime',

        'total_cost', // Nilai Harga Beli barang yang dikembalikan
        'total_discount',
        'total_tax',
        'grand_total', // Nilai bersih yang harus dikembalikan supplier

        'total_refunded',
        'remaining_refund',

        'reason',
        'notes',
    ];

    protected $appends = [
        'status_label',
        'credit_status_label',

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

    // === Status Credit (Pengembalian Dana dari Supplier) ===
    public const RefundStatus_Pending   = 'pending';
    public const RefundStatus_PartiallyRefunded = 'partially_refunded';
    public const RefundStatus_FullyRefunded   = 'fully_refunded';
    public const RefundStatus_NoRefund = 'no_refund';

    public const RefundStatuses = [
        self::RefundStatus_Pending   => 'Menunggu Refund',
        self::RefundStatus_PartiallyRefunded => 'Refund Sebagian',
        self::RefundStatus_FullyRefunded   => 'Refund Lunas',
        self::RefundStatus_NoRefund  => 'Tidak Ada Refund',
    ];

    protected function casts(): array
    {
        return [
            'purchase_order_id' => 'integer',
            'user_id'           => 'integer',
            'supplier_id'       => 'integer',
            'datetime'          => 'datetime',
            'status'            => 'string',
            'refund_status'     => 'string',

            // Kolom Keuangan
            'total_cost'        => 'decimal:2',
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

    public function getCreditStatusLabelAttribute(): string
    {
        return self::RefundStatuses[$this->refund_status] ?? '-';
    }

    // === Relasi ===

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(PurchaseOrderReturnDetail::class);
    }

    public function refunds(): HasMany
    {
        // Meskipun ini adalah 'refund', secara konseptual ini adalah 
        // penerimaan uang kembali (credit) dari supplier.
        return $this->hasMany(PurchaseOrderRefund::class);
    }
}
