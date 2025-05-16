<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ServiceOrder extends Model
{
    use HasFactory;

    /**
     * OrderStatuses
     */
    const OrderStatus_Open = 'open';
    const OrderStatus_Closed = 'closed';
    const OrderStatus_Canceled = 'canceled';

    const OrderStatuses = [
        self::OrderStatus_Open => 'Aktif',
        self::OrderStatus_Closed => 'Selesai',
        self::OrderStatus_Canceled => 'Dibatalkan',
    ];

    /**
     * ServiceStatuses
     */
    const ServiceStatus_Received = 'received';
    const ServiceStatus_Checked = 'checked';
    const ServiceStatus_WaitingParts = 'waiting_parts';
    const ServiceStatus_InProgress = 'in_progress';
    const ServiceStatus_Completed = 'completed';
    const ServiceStatus_Picked = 'picked';

    const ServiceStatuses = [
        self::ServiceStatus_Received => 'Diterima',
        self::ServiceStatus_Checked => 'Diperiksa',
        self::ServiceStatus_WaitingParts => 'Menunggu Sparepart',
        self::ServiceStatus_InProgress => 'Dikerjakan',
        self::ServiceStatus_Completed => 'Selesai',
        self::ServiceStatus_Picked => 'Diambil',
    ];

    /**
     * RepairStatus
     */
    const RepairStatus_NotFinished = 'not_finished';
    const RepairStatus_Success = 'success';
    const RepairStatus_Failed = 'failed';

    const RepairStatuses = [
        self::RepairStatus_NotFinished => 'Belum Selesai',
        self::RepairStatus_Success => 'Sukses',
        self::RepairStatus_Failed => 'Gagal',
    ];

    /**
     * RepairStatus
     */
    const PaymentStatus_Unpaid = 'unpaid';
    const PaymentStatus_PartiallyPaid = 'partially_paid';
    const PaymentStatus_FullyPaid = 'fully_paid';

    const PaymentStatuses = [
        self::PaymentStatus_Unpaid => 'Belum Dibayar',
        self::PaymentStatus_PartiallyPaid => 'Dibayar Sebagian',
        self::PaymentStatus_FullyPaid => 'Lunas',
    ];

    protected $fillable = [
        'customer_id',
        'order_status',
        'service_status',
        'payment_status',
        'repair_status',
        'created_datetime',
        'created_by_uid',
        'closed_datetime',
        'closed_by_uid',
        'updated_datetime',
        'updated_by_uid',
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
        'down_payment',
        'estimated_cost',
        'total_cost',
        'technician_id',
        'warranty_start_date',
        'warranty_day_count',
        'notes'
    ];

    public $timestamps = false;

    /**
     * Get the customer that owns the service order.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }


    public function technician()
    {
        return $this->belongsTo(User::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_uid', 'id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by_uid', 'id');
    }

    public function closedBy()
    {
        return $this->belongsTo(User::class, 'closed_by_uid', 'id');
    }

    public static function activeOrderCount()
    {
        return DB::select(
            "select count(0) as count from service_orders where order_status=?",
            [self::OrderStatus_Open]
        )[0]->count;
    }

    public static function receivedOrderCount()
    {
        return DB::select(
            "select count(0) as count from service_orders where service_status=? and order_status=?",
            [
                self::ServiceStatus_Received,
                self::OrderStatus_Open
            ]
        )[0]->count;
    }

    public static function inProgressCount()
    {
        return DB::select(
            "select count(0) as count from service_orders where service_status=? and order_status=?",
            [self::ServiceStatus_InProgress, self::OrderStatus_Open]
        )[0]->count;
    }

    public static function pickableOrderCount()
    {
        return DB::select(
            "select count(0) as count
                from service_orders
                where service_status=?
                    and order_status=?",
            [
                self::ServiceStatus_Completed,
                self::OrderStatus_Open
            ]
        )[0]->count;
    }

    public static function totalBillable()
    {
        return DB::select(
            "select sum(total_cost-down_payment) as sum
                from service_orders
                where (service_status=? or service_status=?)
                    and payment_status<>?
                    and order_status=?",
            [
                self::ServiceStatus_Completed,
                self::ServiceStatus_Picked,
                self::PaymentStatus_FullyPaid,
                self::OrderStatus_Open
            ]
        )[0]->sum;
    }

    public static function totalActiveBill()
    {
        return DB::select(
            "select sum(total_cost) as sum
                from service_orders
                where (service_status=? or service_status=?)
                    and payment_status<>?
                    and order_status=?",
            [
                self::ServiceStatus_Completed,
                self::ServiceStatus_Picked,
                self::PaymentStatus_FullyPaid,
                self::OrderStatus_Open
            ]
        )[0]->sum;
    }

    public static function totalActiveDownPayment()
    {
        return DB::select(
            "select sum(down_payment) as sum
                from service_orders
                where (service_status=? or service_status=?)
                    and payment_status<>?
                    and order_status=?",
            [
                self::ServiceStatus_Completed,
                self::ServiceStatus_Picked,
                self::PaymentStatus_FullyPaid,
                self::OrderStatus_Open
            ]
        )[0]->sum;
    }

    public static function topCustomers($start_date, $end_date, $limit = 5)
    {
        return DB::select(
            "SELECT c.id AS id,
                c.name AS name,
                SUM(so.total_cost) AS total
            FROM service_orders so
            JOIN customers c ON so.customer_id = c.id
            WHERE so.order_status = ?
                AND DATE(so.closed_datetime) BETWEEN ? AND ?
            GROUP BY c.id, c.name
            ORDER BY total DESC
            LIMIT ?;",
            [
                self::OrderStatus_Closed,
                $start_date,
                $end_date,
                $limit,
            ]
        );
    }

    public static function topTechnicians($start_date, $end_date, $limit = 5)
    {
        return DB::select(
            "SELECT t.id AS id,
              t.name AS name,
              SUM(so.total_cost) AS total
            FROM service_orders so
            JOIN users t ON so.technician_id = t.id
            WHERE so.order_status = ?
              AND DATE(so.closed_datetime) BETWEEN ? AND ?
            GROUP BY t.id, t.name
            ORDER BY total DESC
            LIMIT ?",
            [
                self::OrderStatus_Closed,
                $start_date,
                $end_date,
                $limit,
            ]
        );
    }

    public static function openedOrderByPeriod($start_date, $end_date)
    {
        return DB::select(
            "SELECT
                DATE(created_datetime) AS order_date,
                COUNT(*) AS total_order
            FROM service_orders
            WHERE DATE(created_datetime) BETWEEN ? AND ?
            GROUP BY DATE(created_datetime)
            ORDER BY order_date;",
            [
                $start_date,
                $end_date,
            ]
        );
    }

    public static function closedOrderByPeriod($start_date, $end_date)
    {
        return DB::select(
            "SELECT
                DATE(closed_datetime) AS order_date,
                SUM(total_cost) AS total_order
            FROM service_orders
            WHERE DATE(closed_datetime) BETWEEN ? AND ?
                AND order_status = ?
            GROUP BY DATE(closed_datetime)
            ORDER BY order_date;",
            [
                $start_date,
                $end_date,
                self::OrderStatus_Closed,
            ]
        );
    }

    public static function successOrderByPeriod($start_date, $end_date)
    {
        return DB::select(
            "SELECT
                DATE(completed_datetime) AS order_date,
                COUNT(*) AS total_order
            FROM service_orders
            WHERE DATE(completed_datetime) BETWEEN ? AND ?
                AND repair_status = ?
            GROUP BY DATE(completed_datetime)
            ORDER BY order_date;",
            [
                $start_date,
                $end_date,
                self::RepairStatus_Success
            ]
        );
    }

    public static function failedOrderByPeriod($start_date, $end_date)
    {
        return DB::select(
            "SELECT
                DATE(completed_datetime) AS order_date,
                COUNT(*) AS total_order
            FROM service_orders
            WHERE DATE(completed_datetime) BETWEEN ? AND ?
                AND repair_status = ?
            GROUP BY DATE(completed_datetime)
            ORDER BY order_date;",
            [
                $start_date,
                $end_date,
                self::RepairStatus_Failed
            ]
        );
    }
}
