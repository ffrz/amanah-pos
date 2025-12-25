<?php

/**
 * Proprietary Software / Perangkat Lunak Proprietary
 * Copyright (c) 2025 Fahmi Fauzi Rahman. All rights reserved.
 */

namespace App\Models;

use App\Models\Traits\HasTransactionCode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceOrder extends BaseModel
{
    use HasFactory, HasTransactionCode, SoftDeletes;

    protected string $transactionPrefix = 'SVC'; // Service Code Prefix

    protected $fillable = [
        'order_code',
        'order_status',
        'service_status',
        'payment_status',
        'repair_status',

        'closed_datetime',
        'closed_by_uid',
        'received_by_uid',

        'customer_id',
        'customer_name',
        'customer_phone',
        'customer_address',

        'device_type',
        'device',
        'equipments',
        'device_sn',

        'problems',
        'actions',
        'received_datetime',
        'checked_datetime',
        'worked_datetime',
        'completed_datetime',
        'picked_datetime',

        'warranty_start_date',
        'warranty_day_count',

        'down_payment',
        'estimated_cost',
        'total_cost',

        'images',
        'technician_id',
        'notes',
    ];

    protected $appends = [
        'order_status_label',
        'service_status_label',
        'payment_status_label',
        'repair_status_label',
    ];

    // === Konstanta Status (CamelCase) ===

    public const OrderStatus_Open     = 'open';
    public const OrderStatus_Closed   = 'closed';
    public const OrderStatus_Canceled = 'canceled';

    public const OrderStatuses = [
        self::OrderStatus_Open     => 'Aktif',
        self::OrderStatus_Closed   => 'Selesai',
        self::OrderStatus_Canceled => 'Dibatalkan',
    ];

    public const ServiceStatus_Received  = 'received';
    public const ServiceStatus_Checking  = 'checking';
    public const ServiceStatus_Working   = 'working';
    public const ServiceStatus_Completed = 'completed';
    public const ServiceStatus_Picked    = 'picked';

    public const ServiceStatuses = [
        self::ServiceStatus_Received  => 'Diterima',
        self::ServiceStatus_Checking  => 'Dicek',
        self::ServiceStatus_Working   => 'Dikerjakan',
        self::ServiceStatus_Completed => 'Siap Diambil',
        self::ServiceStatus_Picked    => 'Sudah Diambil',
    ];

    public const PaymentStatus_Unpaid        = 'unpaid';
    public const PaymentStatus_PartiallyPaid = 'partially_paid';
    public const PaymentStatus_FullyPaid     = 'fully_paid';

    public const PaymentStatuses = [
        self::PaymentStatus_Unpaid        => 'Belum Bayar',
        self::PaymentStatus_PartiallyPaid => 'DP / Sebagian',
        self::PaymentStatus_FullyPaid     => 'Lunas',
    ];

    public const RepairStatus_Pending      = 'pending';
    public const RepairStatus_Repairable   = 'repairable';
    public const RepairStatus_Unrepairable = 'unrepairable';

    public const RepairStatuses = [
        self::RepairStatus_Pending      => 'Menunggu Analisa',
        self::RepairStatus_Repairable   => 'Bisa Diperbaiki',
        self::RepairStatus_Unrepairable => 'Tidak Bisa Diperbaiki',
    ];

    protected function casts(): array
    {
        return [
            'order_code'        => 'string',
            'order_status'      => 'string',
            'service_status'    => 'string',
            'payment_status'    => 'string',
            'repair_status'     => 'string',

            'closed_datetime'   => 'datetime',
            'closed_by_uid'     => 'integer',
            'received_by_uid'   => 'integer',

            'customer_id'       => 'integer',
            'customer_name'     => 'string',
            'customer_phone'    => 'string',
            'customer_address'  => 'string',

            'device_type'       => 'string',
            'device'            => 'string',
            'equipments'        => 'string',
            'device_sn'         => 'string',

            'problems'          => 'string',
            'actions'           => 'string',

            'received_datetime'  => 'datetime',
            'checked_datetime'   => 'datetime',
            'worked_datetime'    => 'datetime',
            'completed_datetime' => 'datetime',
            'picked_datetime'    => 'datetime',

            'warranty_start_date' => 'date',
            'warranty_day_count'  => 'integer',

            'down_payment'      => 'decimal:2',
            'estimated_cost'    => 'decimal:2',
            'total_cost'        => 'decimal:2',

            'images'            => 'array',
            'technician_id'     => 'integer',
            'notes'             => 'string',
        ];
    }

    // === Accessors (Label Attributes) ===

    public function getOrderStatusLabelAttribute(): string
    {
        return self::OrderStatuses[$this->order_status] ?? '-';
    }

    public function getServiceStatusLabelAttribute(): string
    {
        return self::ServiceStatuses[$this->service_status] ?? '-';
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        return self::PaymentStatuses[$this->payment_status] ?? '-';
    }

    public function getRepairStatusLabelAttribute(): string
    {
        return self::RepairStatuses[$this->repair_status] ?? '-';
    }

    // === Relationships ===

    public function technician()
    {
        return $this->belongsTo(ServiceTechnician::class, 'technician_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by_uid');
    }

    public function closedBy()
    {
        return $this->belongsTo(User::class, 'closed_by_uid');
    }
}
