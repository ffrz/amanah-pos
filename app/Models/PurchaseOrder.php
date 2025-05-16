<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'supplier_id',
        'status',
        'type',
        'total',
        'notes',
    ];

    // === Status Order ===
    public const Status_Draft     = 'draft';
    public const Status_Approved  = 'approved';
    public const Status_Closed    = 'closed';
    public const Status_Cancelled = 'cancelled';

    public const Statuses = [
        self::Status_Draft     => 'Draft',
        self::Status_Approved  => 'Disetujui',
        self::Status_Closed    => 'Selesai',
        self::Status_Cancelled => 'Dibatalkan',
    ];

    // === Status Pengiriman ===
    public const DeliveryStatus_NotSent  = 'not_sent';
    public const DeliveryStatus_Sent     = 'sent';
    public const DeliveryStatus_Received = 'received';
    public const DeliveryStatus_Failed   = 'failed';

    public const DeliveryStatuses = [
        self::DeliveryStatus_NotSent  => 'Draft',
        self::DeliveryStatus_Sent     => 'Disetujui',
        self::DeliveryStatus_Received => 'Selesai',
        self::DeliveryStatus_Failed   => 'Dibatalkan',
    ];

    // === Status Pembayaran ===
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
        return $this->hasMany(PurchaseOrderDetail::class, 'parent_id');
    }
}
