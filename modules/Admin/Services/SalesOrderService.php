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
use App\Models\StockMovement;
use App\Models\UserActivityLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SalesOrderService
{
    public function __construct(
        protected SalesOrderPaymentService $paymentService,
        protected UserActivityLogService $userActivityLogService,
        protected DocumentVersionService $documentVersionService,
        protected FinanceTransactionService $financeTransactionService,
        protected StockMovementService $stockMovementService,
        protected ProductService $productService,
        protected SupplierService $supplierService,
    ) {}

    public function getData(array $options)
    {
        $orderBy = $options['order_by'];
        $orderType = $options['order_type'];
        $filter = $options['filter'];

        $q = SalesOrder::with(['customer', 'details', 'details.product', 'cashier', 'cashierSession.cashierTerminal']);

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

            $statuses = $filter['status'];
            $q->where(function ($q) use ($statuses) {
                foreach ($statuses as $status) {
                    $q->orWhere('status', '=', $status);
                }
            });
        }

        if (!empty($filter['payment_status']) && $filter['payment_status'] != 'all') {
            $q->where('payment_status', '=', $filter['payment_status']);
        }

        if (!empty($filter['delivery_status']) && $filter['delivery_status'] != 'all') {
            $q->where('delivery_status', '=', $filter['delivery_status']);
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

    public function createOrder(): SalesOrder
    {
        $item = new SalesOrder([
            'type' => SalesOrder::Type_Pickup,
            'datetime' => Carbon::now(),
            'status' => SalesOrder::Status_Draft,
            'payment_status' => SalesOrder::PaymentStatus_Unpaid,
            'delivery_status' => SalesOrder::DeliveryStatus_ReadyForPickUp,
        ]);
        $item->cashier_id = Auth::user()->id;
        $item->save();
        return $item;
    }

    public function findOrderOrFail(int $id): SalesOrder
    {
        return SalesOrder::with(['details', 'customer'])->findOrFail($id);
    }

    public function editOrder(SalesOrder $order): SalesOrder
    {
        $this->ensureOrderIsEditable($order);

        return $order;
    }

    public function updateOrder(SalesOrder $item, array $data): SalesOrder
    {
        $this->ensureOrderIsEditable($item);

        if (isset($data['customer_id'])) {
            $customer = $data['customer_id'] ? Customer::findOrFail($data['customer_id']) : null;

            // WARNING: logika ini perlu diperbarui jika mendukung customization di frontend
            // saat ini info customer selalu diperbarui dari data customer
            // karena frontend tidak mendukung customization
            if ($customer) {
                $item->customer_id      = $customer->id;
                $item->customer_code    = $customer->code;
                $item->customer_name    = $customer->name;
                $item->customer_phone   = $customer->phone;
                $item->customer_address = $customer->address;
            }
        }

        $item->notes = $data['notes'];
        $item->datetime = $data['datetime'];

        return DB::transaction(function () use ($item) {
            $item->save();
            // kita tidak mencatat log dan lacak version di penyimpanan ini
            // untuk menghemat ruang dan meminimalisir interaksi database
            return $item;
        });
    }

    public function cancelOrder(SalesOrder $item): SalesOrder
    {
        $this->ensureOrderIsEditable($item);

        $item->status = SalesOrder::Status_Canceled;

        return DB::transaction(function () use ($item) {
            $item->save();

            $this->documentVersionService->createVersion($item);

            $this->userActivityLogService->log(
                UserActivityLog::Category_SalesOrder,
                UserActivityLog::Name_SalesOrder_Cancel,
                "Order penjualan $item->formatted_id telah dibatalkan.",
                [
                    'data' => $item->toArray(),
                    'formatter' => 'sales-order',
                ]
            );

            return $item;
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

    private function processSalesOrderStockOut(SalesOrder $order)
    {
        // 5. Perbarui stok produk secara massal
        foreach ($order->details as $detail) {
            $productType = $detail->product->type;
            if (
                $productType == Product::Type_NonStocked
                || $productType == Product::Type_Service
            ) {
                continue;
            }

            $product = $detail->product;

            $this->stockMovementService->processStockOut([
                'product_id'      => $detail->product_id,
                'product_name'    => $detail->product_name,
                'uom'             => $detail->product_uom,
                'ref_id'          => $detail->id,
                'ref_type'        => StockMovement::RefType_SalesOrderDetail,
                'quantity'        => $detail->quantity,
                'quantity_before' => $product->stock,
                'quantity_after'  => $product->stock + $detail->quantity,
                'notes'           => "Transaksi penjualan #$order->formatted_id",
            ]);
        }
    }

    private function updateTotalAndValidateClientTotal($order, $client_total)
    {
        $order->updateTotals();
        $clientTotal = intval($client_total);
        $serverTotal = intval($order->grand_total);
        if ($serverTotal !== $clientTotal) {
            throw new BusinessRuleViolationException('Gagal menyimpan transaksi, total tidak sinkron. Coba refresh halaman!');
        }
    }

    private function ensureOrderIsEditable(SalesOrder $order)
    {
        if ($order->status != SalesOrder::Status_Draft) {
            throw new BusinessRuleViolationException('Order sudah tidak dapat diubah.');
        }
    }
}
