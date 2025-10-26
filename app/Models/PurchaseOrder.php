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
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends BaseModel
{
    use HasDocumentVersions,
        HasFactory,
        HasTransactionCode,
        SoftDeletes;

    protected string $transactionPrefix = 'PO';

    protected $fillable = [
        'code',
        'supplier_id',
        'supplier_code',
        'supplier_name',
        'supplier_phone',
        'supplier_address',

        'type',

        'datetime',
        'due_date',
        'status',
        'payment_status',
        'delivery_status',

        'total',
        'total_paid',
        'total_discount',
        'total_tax',
        'grand_total',
        'total_return',
        'balance',
        'notes',
    ];

    protected $appends = [
        'status_label',
        'payment_status_label',
        'delivery_status_label',
    ];

    public const Type_Pickup   = 'pickup';
    public const Type_Delivery = 'delivery';

    public const Types = [
        self::Type_Pickup   => 'Diambil',
        self::Type_Delivery => 'Dikirim',
    ];

    public const Status_Draft     = 'draft';
    public const Status_Closed    = 'closed';
    public const Status_Canceled = 'canceled';

    public const Statuses = [
        self::Status_Draft    => 'Draft',
        self::Status_Closed   => 'Selesai',
        self::Status_Canceled => 'Dibatalkan',
    ];

    public const PaymentStatus_Unpaid        = 'unpaid';
    public const PaymentStatus_PartiallyPaid = 'partially_paid';
    public const PaymentStatus_FullyPaid     = 'fully_paid';
    // public const PaymentStatus_Refunded      = 'refunded';

    public const PaymentStatuses = [
        self::PaymentStatus_Unpaid         => 'Belum Lunas',
        self::PaymentStatus_PartiallyPaid  => 'Dibayar Sebagian',
        self::PaymentStatus_FullyPaid      => 'Lunas',
        // self::PaymentStatus_Refunded       => 'Dikembalikan',
    ];

    // === Status Pengiriman ===
    public const DeliveryStatus_NotSent  = 'not_sent';
    public const DeliveryStatus_Sent     = 'sent';
    public const DeliveryStatus_Received = 'received';
    public const DeliveryStatus_Failed   = 'failed';

    public const DeliveryStatus_ReadyForPickUp = 'ready_for_pickup';
    public const DeliveryStatus_PickedUp = 'picked_up';

    public const DeliveryStatuses = [
        self::DeliveryStatus_NotSent  => 'Belum Dikirim',
        self::DeliveryStatus_Sent     => 'Sedang Dikirim',
        self::DeliveryStatus_Received => 'Terkirim',
        self::DeliveryStatus_Failed   => 'Gagal',

        self::DeliveryStatus_ReadyForPickUp => 'Siap Diambil',
        self::DeliveryStatus_PickedUp       => 'Sudah Diambil',
    ];

    protected function casts(): array
    {
        return [
            'type' => 'string',

            'supplier_id'       => 'integer',
            'supplier_code'     => 'string',
            'supplier_name'     => 'string',
            'supplier_phone'    => 'string',
            'supplier_address'  => 'string',

            'datetime'        => 'datetime',
            'due_date'        => 'date',
            'status'          => 'string',
            'payment_status'  => 'string',
            'delivery_status' => 'string',
            'total'           => 'decimal:2',
            'total_paid'      => 'decimal:2',

            'total_discount'  => 'decimal:2',
            'total_tax'       => 'decimal:2',

            'grand_total'     => 'decimal:2',
            'total_return'    => 'decimal:2',
            'balance'         => 'decimal:2',

            'notes'           => 'string',
            'created_by' => 'integer',
            'updated_by' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    protected function getRemainingDebtAttribute(): float
    {
        return $this->grand_total - $this->total_return - $this->total_paid;
    }

    public function getTypeLabelAttribute()
    {
        return self::Types[$this->type] ?? '-';
    }

    public function getStatusLabelAttribute()
    {
        return self::Statuses[$this->status] ?? '-';
    }

    public function getPaymentStatusLabelAttribute()
    {
        return self::PaymentStatuses[$this->payment_status] ?? '-';
    }

    public function getDeliveryStatusLabelAttribute()
    {
        return self::DeliveryStatuses[$this->delivery_status] ?? '-';
    }

    public function details()
    {
        return $this->hasMany(PurchaseOrderDetail::class, 'order_id')
            ->whereNull('return_id');
    }

    public function payments()
    {
        return $this->hasMany(PurchaseOrderPayment::class, 'order_id')
            ->whereNull('return_id');
    }

    public function returns()
    {
        return $this->hasMany(PurchaseOrderReturn::class, 'purchase_order_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function updateGrandTotal()
    {
        $this->total = $this->details()->sum('subtotal_cost');
        $this->grand_total = $this->total + $this->total_tax - $this->total_discount;
    }

    public function updateTotals()
    {
        $this->total_return = PurchaseOrderReturn::where('purchase_order_id', $this->id)
            ->where('status', PurchaseOrderReturn::Status_Closed)
            ->sum('grand_total');
        $this->total_paid = PurchaseOrderPayment::where('order_id', $this->id)
            ->sum('amount');
        $this->balance = - ($this->grand_total - $this->total_paid - $this->total_return);

        if ($this->balance >= 0) {
            $this->payment_status = self::PaymentStatus_FullyPaid;
        } elseif ($this->total_paid > 0) {
            $this->payment_status = self::PaymentStatus_PartiallyPaid;
        } else {
            $this->payment_status = self::PaymentStatus_Unpaid;
        }
    }
}
