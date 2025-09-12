<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SalesOrder extends BaseModel
{
    protected $fillable = [
        'customer_id',
        'datetime',
        'due_date',
        'status',
        'payment_status',
        'delivery_status',
        'total_cost',
        'total_price',
        'total_paid',
        'grand_total',
        'remaining_debt',
        'change',
        'notes',
    ];

    protected $appends = [
        'status_label',
        'payment_status_label',
        'delivery_status_label',
        'formatted_id',
    ];

    // === Status Order ===
    public const Status_Draft     = 'draft';
    public const Status_Closed    = 'closed';
    public const Status_Canceled = 'canceled';

    public const Statuses = [
        self::Status_Draft     => 'Draft',
        self::Status_Closed    => 'Selesai',
        self::Status_Canceled => 'Dibatalkan',
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

    // === Status Pengiriman ===
    public const DeliveryStatus_NotSent  = 'not_sent';
    public const DeliveryStatus_Sent     = 'sent';
    public const DeliveryStatus_Received = 'received';
    public const DeliveryStatus_Failed   = 'failed';

    public const DeliveryStatuses = [
        self::DeliveryStatus_NotSent  => 'Belum Dikirim',
        self::DeliveryStatus_Sent     => 'Sedang Dikirim',
        self::DeliveryStatus_Received => 'Terkirim',
        self::DeliveryStatus_Failed   => 'Gagal',
    ];

    protected function casts(): array
    {
        return [
            'customer_id'     => 'integer',
            'datetime'        => 'datetime',
            'due_date'        => 'datetime',
            'status'          => 'string',
            'payment_status'  => 'string',
            'delivery_status' => 'string',
            'total_cost'      => 'decimal:2',
            'total_price'     => 'decimal:2',
            'total_paid'      => 'decimal:2',

            'grand_total'     => 'decimal:2',
            'remaining_debt'  => 'decimal:2',
            'change'          => 'decimal:2',

            'notes'           => 'string',
            'created_by' => 'integer',
            'updated_by' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    protected function getTotalPriceAttribute(string $value): float
    {
        return (float) $value;
    }

    protected function getTotalCostAttribute(string $value): float
    {
        return (float) $value;
    }

    protected function getTotalPaidAttribute(string $value): float
    {
        return (float) $value;
    }

    public function getFormattedIdAttribute()
    {
        return Setting::value('sales_order_code_prefix', 'SO-')
            . Carbon::parse($this->created_at)->format('Ymd')
            . '-'
            . $this->id;
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
        return $this->hasMany(SalesOrderDetail::class, 'parent_id');
    }

    public function payments()
    {
        return $this->hasMany(SalesOrderPayment::class, 'order_id');
    }

    /**
     * Get the supplier for the product.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
