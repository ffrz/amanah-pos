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

namespace Modules\Admin\Services;

use App\Exceptions\BusinessRuleViolationException;
use App\Models\Customer;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\SalesOrderReturn;
use App\Models\Setting;
use App\Models\StockMovement;
use App\Models\UserActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SalesOrderReturnService
{
    public function __construct(
        protected SalesOrderPaymentService $paymentService,
        protected UserActivityLogService $userActivityLogService,
        protected DocumentVersionService $documentVersionService,
        protected FinanceTransactionService $financeTransactionService,
        protected StockMovementService $stockMovementService,
        protected ProductService $productService,
        protected CustomerService $customerService,
        protected CashierSessionService $cashierSessionService,
    ) {}

    // OK
    public function getData(array $options)
    {
        $orderBy = $options['order_by'];
        $orderType = $options['order_type'];
        $filter = $options['filter'];

        $q = SalesOrderReturn::with(['salesOrder', 'customer', 'details', 'details.product']);

        if (!empty($filter['search'])) {
            $q->where(function ($q) use ($filter) {
                $q->orWhere('notes', 'like', '%' . $filter['search'] . '%');
                $q->orWhere('customer_code', 'like', '%' . $filter['search'] . '%');
                $q->orWhere('customer_name', 'like', '%' . $filter['search'] . '%');
                $q->orWhere('customer_phone', 'like', '%' . $filter['search'] . '%');
                $q->orWhere('customer_address', 'like', '%' . $filter['search'] . '%');
            });

            $q->orWhereHas('details.product', function ($q) use ($filter) {
                $q->where('name', 'like', "%" . $filter['search'] . "%")
                    ->orWhere('barcode', 'like', "%" . $filter['search'] . "%");
            });
        }

        if (!empty($filter['status']) && $filter['status'] != 'all') {
            if (!is_array($filter['status'])) {
                $filter['status'] = [$filter['status']];
            }
            $q->whereIn('status', $filter['status']);
        }

        if (!empty($filter['refund_status']) && $filter['refund_status'] != 'all') {
            $q->where('refund_status', '=', $filter['refund_status']);
        }

        if (!empty($filter['year']) && $filter['year'] !== 'all') {
            $q->whereYear('datetime', $filter['year']);

            if (!empty($filter['month']) && $filter['month'] !== 'all') {
                $q->whereMonth('datetime', $filter['month']);
            }
        }

        if (!empty($filter['customer_id']) && $filter['customer_id'] !== 'all') {
            $q->where('customer_id', $filter['customer_id']);
        }

        if (!empty($filter['cashier_session_id']) && $filter['cashier_session_id'] !== 'all') {
            $q->where('cashier_session_id', $filter['cashier_session_id']);
        }

        // $q->select(['id', 'total_price', 'datetime', 'status', 'payment_status', 'delivery_status'])
        $q->orderBy($orderBy, $orderType);

        return $q->paginate($options['per_page']);
    }

    public function createOrderReturn(SalesOrder $order): SalesOrderReturn
    {
        if ($order->status !== SalesOrder::Status_Closed) {
            throw new BusinessRuleViolationException('Transaksi belum selesai tidak dapat diretur!');
        }

        $item = new SalesOrderReturn([
            'sales_order_id' => $order->id,
            'customer_id' => $order->customer_id,
            'customer_name' => $order->customer_name,
            'customer_phone' => $order->customer_phone,
            'customer_address' => $order->customer_address,
            'status' => SalesOrderReturn::Status_Draft,
            'refund_status' => SalesOrderReturn::RefundStatus_Pending,
            'grand_total' => $order->grand_total,
            'datetime' => now(),
        ]);
        $item->save();
        return $item;
    }

    // OK
    public function findOrderOrFail(int $id, $relations = ['salesOrder', 'details', 'customer'], $cols = "*"): SalesOrderReturn
    {
        return SalesOrderReturn::with($relations)->findOrFail($id, $cols);
    }

    // OK
    public function editOrder(SalesOrderReturn $order): SalesOrderReturn
    {
        $this->ensureOrderIsEditable($order);

        return $order;
    }

    // OK
    public function updateOrder(SalesOrderReturn $item, array $data): SalesOrderReturn
    {
        $this->ensureOrderIsEditable($item);

        $item->notes = $data['notes'];
        $item->datetime = $data['datetime'];

        return DB::transaction(function () use ($item) {
            $item->save();
            return $item;
        });
    }

    // OK
    public function cancelOrder(SalesOrderReturn $orderReturn): SalesOrderReturn
    {
        $this->ensureOrderIsEditable($orderReturn);

        $orderReturn->status = SalesOrderReturn::Status_Canceled;

        return DB::transaction(function () use ($orderReturn) {
            $orderReturn->save();

            $this->documentVersionService->createVersion($orderReturn);

            $this->userActivityLogService->log(
                UserActivityLog::Category_SalesOrderReturn,
                UserActivityLog::Name_SalesOrderReturn_Cancel,
                "Order penjualan $orderReturn->formatted_id telah dibatalkan.",
                [
                    'data' => $orderReturn->toArray(),
                    'formatter' => 'sales-order-return',
                ]
            );

            return $orderReturn;
        });
    }

    public function getOrderWithDetails($id): SalesOrder
    {
        return SalesOrder::with([
            'cashier',
            'customer',
            'details',
            'payments',
            'payments.account',
            'cashierSession',
            'cashierSession.cashierTerminal'
        ])
            ->findOrFail($id);
    }

    public function closeOrder(SalesOrderReturn $order, array $data)
    {
        $this->ensureOrderIsEditable($order);

        DB::transaction(function () use ($order, $data) {
            $cashierSession = $this->cashierSessionService->getActiveSession();

            $this->updateTotalAndValidateClientTotal($order, $data['total'] ?? 0);

            $order->status = SalesOrderReturn::Status_Closed;
            $order->remaining_debt = $order->grand_total;
            $order->due_date = $data['due_date'] ?? null;
            $order->cashier_id = Auth::user()->id;
            $order->cashier_session_id = $cashierSession ? $cashierSession->id : null;

            if ($order->customer_id) {
                // di awal kita catat sebagai utang dulu
                // Utang customer adalah nilai negatif
                Customer::where('id', $order->customer_id)
                    ->decrement('balance', $order->grand_total);
            }

            // Simpan dan proses pembayaran
            $this->paymentService->addPayments($order, $data['payments'] ?? []);

            // Update kembalian
            $order->change = max(0, $order->total_paid - $order->grand_total);
            $order->save();

            // Perbarui stok produk secara massal
            $this->processSalesOrderReturnStockIn($order);

            // update settings
            // FIXME: seharusnya ini dipindahkan ke user settings agar tidak semua user terdampak
            Setting::setValue('pos.after_payment_action', $data['after_payment_action'] ?? 'print');
        });
    }

    // IN PROGRESS
    public function deleteOrder(SalesOrderReturn $order)
    {
        DB::transaction(function () use ($order) {
            if ($order->status == SalesOrderReturn::Status_Closed) {
                // BELUM DIVALIDASI
                $this->reverseStock($order);

                if ($order->customer_id) {
                    $this->customerService->addToBalance($order->customer, abs($order->grand_total));
                }

                $this->paymentService->deletePayments($order);
            }
            $order->delete();
        });
    }

    private function updateTotalAndValidateClientTotal($order, $client_total)
    {
        // Hitung total biaya dan harga di server
        $total_cost = $order->details->sum(function ($detail) {
            return round($detail->cost * $detail->quantity);
        });

        // Lakukan hal yang sama untuk total_price
        $total_price = $order->details->sum(function ($detail) {
            return round($detail->price * $detail->quantity);
        });

        $order->total_cost = $total_cost;
        $order->total_price = $total_price;
        $order->grand_total = $order->total_price;

        // Validasi grand total dengan input dari klien
        $clientTotal = intval($client_total);
        $serverTotal = intval($order->grand_total);
        if ($serverTotal !== $clientTotal) {
            throw new BusinessRuleViolationException('Gagal menyimpan transaksi, data tidak sinkron, coba refresh halaman!');
        }
    }

    // OK
    private function ensureOrderIsEditable(SalesOrderReturn $orderReturn)
    {
        if ($orderReturn->status != SalesOrderReturn::Status_Draft) {
            throw new BusinessRuleViolationException('Order sudah tidak dapat diubah.');
        }
    }

    // OK
    private function processSalesOrderReturnStockIn(SalesOrder $order)
    {
        foreach ($order->details as $detail) {
            $productType = $detail->product->type;
            if (
                $productType == Product::Type_NonStocked
                || $productType == Product::Type_Service
            ) {
                continue;
            }

            $quantity = $detail->quantity;

            StockMovement::create([
                'product_id'      => $detail->product_id,
                'product_name'    => $detail->product_name,
                'uom'             => $detail->product_uom,
                'ref_id'          => $detail->id,
                'ref_type'        => StockMovement::RefType_SalesOrderReturnDetail,
                'quantity'        => $quantity,
                'quantity_before' => $detail->product->stock,
                'quantity_after'  => $detail->product->stock - $quantity,
                'notes'           => "Transaksi retur penjualan #$order->formatted_id",
            ]);

            Product::where('id', $detail->product_id)->increment('stock', $quantity);
        }
    }

    // OK
    private function reverseStock(SalesOrder $order)
    {
        foreach ($order->details as $detail) {
            Product::where('id', $detail->product_id)->decrement('stock', $detail->quantity);

            StockMovement::where('ref_type', StockMovement::RefType_SalesOrderReturnDetail)
                ->where('ref_id', $detail->id)
                ->delete();
        }
    }
}
