<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WashOrder extends Model
{
    use HasFactory;
    
    protected $table = 'wash_orders';

    public $timestamps = false;

    protected $fillable = [
        'customer_id',
        'customer_name',
        'customer_phone',
        'customer_address',
        'vehicle_plate_number',
        'vehicle_description',
        'order_created_at',
        'work_started_at',
        'work_completed_at',
        'order_closed_at',
        'order_status',
        'service_status',
        'payment_status',
        'total_price',
        'notes',
    ];

    // === Order Status ===
    public const OrderStatus_Pending     = 'pending';
    public const OrderStatus_Confirmed   = 'confirmed';
    public const OrderStatus_Completed   = 'completed';
    public const OrderStatus_Canceled    = 'canceled';

    public const OrderStatuses = [
        self::OrderStatus_Pending     => 'Menunggu',
        self::OrderStatus_Confirmed   => 'Dikonfirmasi',
        self::OrderStatus_Completed   => 'Selesai',
        self::OrderStatus_Canceled    => 'Dibatalkan',
    ];

    // === Service Status ===
    public const ServiceStatus_NotStarted = 'not_started';
    public const ServiceStatus_InProgress = 'in_progress';
    public const ServiceStatus_Finished   = 'finished';
    public const ServiceStatus_Failed     = 'failed';

    public const ServiceStatuses = [
        self::ServiceStatus_NotStarted  => 'Belum Dimulai',
        self::ServiceStatus_InProgress  => 'Sedang Dikerjakan',
        self::ServiceStatus_Finished    => 'Selesai',
        self::ServiceStatus_Failed      => 'Dibatalkan',
    ];

    // === Payment Status ===
    public const PaymentStatus_Unpaid   = 'unpaid';
    public const PaymentStatus_Paid     = 'paid';
    public const PaymentStatus_Partial  = 'partial';
    public const PaymentStatus_Refunded = 'refunded';

    public const PaymentStatuses = [
        self::PaymentStatus_Unpaid   => 'Belum Dibayar',
        self::PaymentStatus_Paid     => 'Sudah Dibayar',
        self::PaymentStatus_Partial  => 'Dibayar Sebagian',
        self::PaymentStatus_Refunded => 'Dikembalikan',
    ];

    // === Relasi ===

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_uid');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by_uid');
    }

    public function details()
    {
        return $this->hasMany(WashOrderDetail::class, 'order_id');
    }
}
