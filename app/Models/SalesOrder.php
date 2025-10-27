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
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesOrder extends BaseModel
{
    use HasDocumentVersions,
        HasTransactionCode,
        SoftDeletes;

    protected string $transactionPrefix = 'SO';

    protected $fillable = [
        'cashier_id',
        'cashier_session_id',
        'code',
        'customer_id',
        'customer_code',
        'customer_name',
        'customer_phone',
        'customer_address',

        'type',

        'datetime',
        'due_date',
        'status',
        'payment_status',
        'delivery_status',
        'total_cost',
        'total_price',
        'total_discount',
        'total_tax',

        'grand_total',
        'total_paid',
        'total_return',
        'balance',

        'change',
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
    public const PaymentStatus_Refunded      = 'refunded';

    public const PaymentStatuses = [
        self::PaymentStatus_Unpaid         => 'Belum Lunas',
        self::PaymentStatus_PartiallyPaid  => 'Dibayar Sebagian',
        self::PaymentStatus_FullyPaid      => 'Lunas',
        self::PaymentStatus_Refunded       => 'Dikembalikan',
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

            'cashier_id'        => 'integer',

            'customer_id'       => 'integer',
            'customer_code' => 'string',
            'customer_name'     => 'string',
            'customer_phone'    => 'string',
            'customer_address'  => 'string',

            'datetime'        => 'datetime',
            'due_date'        => 'date',
            'status'          => 'string',
            'payment_status'  => 'string',
            'delivery_status' => 'string',
            'total_cost'      => 'decimal:2',
            'total_price'     => 'decimal:2',

            'total_discount'  => 'decimal:2',
            'total_tax'       => 'decimal:2',

            'change'          => 'decimal:2',

            'grand_total'     => 'decimal:2',
            'total_paid'      => 'decimal:2',
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
        return $this->hasMany(SalesOrderDetail::class, 'order_id')
            ->whereNull('return_id');
    }

    public function payments()
    {
        return $this->hasMany(SalesOrderPayment::class, 'order_id');
    }

    public function returns()
    {
        return $this->hasMany(SalesOrderReturn::class, 'sales_order_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function cashier()
    {
        return $this->belongsTo(User::class);
    }

    public function cashierSession()
    {
        return $this->belongsTo(CashierSession::class, 'cashier_session_id');
    }

    public function updateTotals()
    {
        $this->total_return = SalesOrderReturn::where('sales_order_id', $this->id)
            ->where('status', SalesOrderReturn::Status_Closed)
            ->sum('grand_total');

        $this->total_paid = SalesOrderPayment::where('order_id', $this->id)
            ->sum('amount');

        // dd($this->grand_total, $this->total_paid, $this->total_return);
        // $this->balance = - ($this->grand_total - $this->total_paid - $this->total_return);
        // if ($this->grand_total == $this->total_return) {
        //     dd('this is wrong!!');
        //     $this->balance = - ($this->grand_total + $this->total_paid);
        // } else {

        // }
        $this->balance = - ($this->grand_total - $this->total_return - $this->total_paid);

        if ($this->balance >= 0) {
            $this->payment_status = self::PaymentStatus_FullyPaid;
        } elseif ($this->total_paid > 0) {
            $this->payment_status = self::PaymentStatus_PartiallyPaid;
        } else {
            $this->payment_status = self::PaymentStatus_Unpaid;
        }
    }

    public function updateGrandTotal(): void
    {
        $this->total_cost  = $this->details()->sum('subtotal_cost');
        $this->total_price = $this->details()->sum('subtotal_price');
        $this->grand_total = $this->total_price + $this->total_tax - $this->total_discount;
    }
}
