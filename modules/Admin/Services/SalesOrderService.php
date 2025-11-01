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
        protected CustomerService $customerService,
        protected CashierSessionService $cashierSessionService,
    ) {}

    public function getData(array $options)
    {
        $orderBy = $options['order_by'];
        $orderType = $options['order_type'];
        $filter = $options['filter'];

        $q = SalesOrder::with(['customer', 'details', 'details.product', 'cashier', 'cashierSession.cashierTerminal']);

        if (!empty($filter['search'])) {
            $q->where(function ($q) use ($filter) {
                $q->orWhere('code', 'like', "%" . $filter['search'] . "%");
                $q->orWhere('notes', 'like', '%' . $filter['search'] . '%');
                $q->orWhere('customer_code', 'like', '%' . $filter['search'] . '%');
                $q->orWhere('customer_name', 'like', '%' . $filter['search'] . '%');
                $q->orWhere('customer_phone', 'like', '%' . $filter['search'] . '%');
                $q->orWhere('customer_address', 'like', '%' . $filter['search'] . '%');
            });

            $q->orWhereHas('details.product', function ($q) use ($filter) {
                $q->orWhere('name', 'like', "%" . $filter['search'] . "%");
                $q->orWhere('barcode', 'like', "%" . $filter['search'] . "%");
            });
        }

        if (!empty($filter['status']) && $filter['status'] != 'all') {
            if (!is_array($filter['status'])) {
                $filter['status'] = [$filter['status']];
            }
            $q->whereIn('status', $filter['status']);
        }

        if (!empty($filter['payment_status']) && $filter['payment_status'] != 'all') {
            $q->where('payment_status', '=', $filter['payment_status']);
        }

        if (!empty($filter['delivery_status']) && $filter['delivery_status'] != 'all') {
            $q->where('delivery_status', '=', $filter['delivery_status']);
        }

        if (!empty($filter['start_date'])) {
            $q->whereDate('datetime', '>=', $filter['start_date']);
        }

        if (!empty($filter['end_date'])) {
            $q->where('datetime', '<=', $filter['end_date'] . ' 23:59:59');
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
        $item = SalesOrder::where('status', SalesOrder::Status_Draft)
            ->where('created_by', Auth::user()->id)
            ->where('grand_total', 0)
            ->orderBy('id', 'desc')
            ->first();

        if ($item) {
            return $item;
        }

        $item = new SalesOrder([
            'type' => SalesOrder::Type_Pickup,
            'datetime' => Carbon::now(),
            'status' => SalesOrder::Status_Draft,
            'payment_status' => SalesOrder::PaymentStatus_Unpaid,
            'delivery_status' => SalesOrder::DeliveryStatus_ReadyForPickUp,
        ]);
        $activeSession = $this->cashierSessionService->getActiveSession();
        $item->cashier_id = Auth::user()->id;
        $item->cashier_session_id = $activeSession ? $activeSession->id : null;
        $item->save();
        return $item;
    }

    public function findOrderOrFail(int $id): SalesOrder
    {
        return SalesOrder::with(['details', 'details.product', 'customer', 'cashierSession', 'cashierSession.cashierTerminal'])->findOrFail($id);
    }

    public function editOrder(SalesOrder $order): SalesOrder
    {
        $this->ensureOrderIsEditable($order);

        return $order;
    }

    public function updateOrder(SalesOrder $item, array $data): SalesOrder
    {
        $this->ensureOrderIsEditable($item);

        $customer = !empty($data['customer_id']) ? Customer::findOrFail($data['customer_id']) : null;
        $item->customer_id      = $customer?->id;
        $item->customer_code    = $customer?->code;
        $item->customer_name    = $customer?->name;
        $item->customer_phone   = $customer?->phone;
        $item->customer_address = $customer?->address;

        $activeSession = $this->cashierSessionService->getActiveSession();
        $item->cashier_session_id = $activeSession ? $activeSession->id : null;
        $item->notes = $data['notes'];
        $item->datetime = $data['datetime'];

        return DB::transaction(function () use ($item) {
            $item->updateGrandTotal();
            $item->updateTotals();
            $item->save();
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
                "Order penjualan $item->code telah dibatalkan.",
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

    public function closeOrder(SalesOrder $order, array $data)
    {
        $this->ensureOrderIsEditable($order);

        DB::transaction(function () use ($order, $data) {
            $cashierSession = $this->cashierSessionService->getActiveSession();

            $this->updateTotalAndValidateClientTotal($order, $data['total'] ?? 0);

            $order->status = SalesOrder::Status_Closed;
            $order->delivery_status = SalesOrder::DeliveryStatus_PickedUp;
            $order->due_date = $data['due_date'] ?? null;
            $order->cashier_id = Auth::user()->id;
            $order->cashier_session_id = $cashierSession ? $cashierSession->id : null;

            // Logika handle kembalian
            if (isset($data['change']) && $data['change'] > 0) {
                // hitung ulang di server agar lebih aman
                $order->change = $data['change'];
                $data['payments'] = $this->adjustCashPaymentForChange($order, $data['payments']);
            }

            // Simpan dan proses pembayaran
            $this->paymentService->addPayments($order, $data['payments'] ?? [], false);

            // save order
            $order->updateTotals();
            $order->save();

            // catat utang piutang
            if ($order->customer_id && $order->balance != 0) {
                $this->customerService->addToBalance($order->customer, $order->balance);
            }

            // Perbarui stok produk secara massal
            $this->processSalesOrderStockOut($order);

            // TODO: seharusnya ini dipindahkan ke user settings agar tidak semua user terdampak
            Setting::setValue('pos.after_payment_action', $data['after_payment_action'] ?? 'print');
        });
    }

    /**
     * Menyesuaikan jumlah pembayaran tunai dengan memotong jumlah kembalian (change).
     * Kembalian harus selalu dipotong dari pembayaran 'cash'.
     *
     * @param SalesOrder $order Model Order yang sudah memiliki 'change' yang benar.
     * @param array $payments Array pembayaran dari input $data.
     * @return array Array pembayaran yang sudah disesuaikan.
     */
    private function adjustCashPaymentForChange(SalesOrder $order, array $payments): array
    {
        // Pastikan ada kembalian
        if ($order->change <= 0) {
            return $payments; // Tidak perlu penyesuaian
        }

        $cashIndex = -1;
        $cashAmount = 0;
        $cashPaymentCount = 0;

        // Cari indeks pembayaran 'cash' dan total jumlahnya
        foreach ($payments as $index => $payment) {
            if ($payment['id'] === 'cash') {

                if ($cashIndex !== -1) {
                    // Melarang lebih dari satu entri cash
                    throw new BusinessRuleViolationException('Hanya boleh ada 1 pembayaran tunai per transaksi.');
                }

                $cashPaymentCount++;
                $cashIndex = $index;
                $cashAmount += $payment['amount'];
            }
        }

        if ($cashIndex === -1) {
            throw new BusinessRuleViolationException('Kembalian hanya berlaku untuk transaksi tunai.');
        }

        $newCashAmount = $cashAmount - $order->change;

        if ($newCashAmount < 0) {
            throw new BusinessRuleViolationException('Jumlah tunai yang dibayarkan tidak mencukupi untuk menutupi kembalian yang dibutuhkan. Transaksi overpayment tidak didukung!');
        }

        $payments[$cashIndex]['amount'] = $newCashAmount;

        return $payments;
    }

    public function deleteOrder(SalesOrder $order)
    {
        $cashierSession = $order->cashierSession;
        if ($order->status == SalesOrder::Status_Closed && $cashierSession && $cashierSession->is_closed) {
            throw new BusinessRuleViolationException("Transaksi tidak dapat dihapus! Sesi kasir untuk Order #$order->code sudah ditutup!");
        }

        if (SalesOrderReturn::where('sales_order_id', $order->id)->exists()) {
            throw new BusinessRuleViolationException('Tidak dapat dihapus karena ada transaksi retur!');
        }

        DB::transaction(function () use ($order) {
            if ($order->status == SalesOrder::Status_Closed) {
                $this->reverseStock($order);
                $this->paymentService->deletePayments($order);
            }

            $order->delete();

            if ($order->status == SalesOrder::Status_Closed) {
                if ($order->customer_id && $order->balance != 0) {
                    $this->customerService->addToBalance($order->customer, abs($order->grand_total));
                }
            }
        });
    }

    private function updateTotalAndValidateClientTotal(SalesOrder $order, $client_total)
    {
        $order->updateGrandTotal();

        // Validasi grand total dengan input dari klien
        $clientTotal = intval($client_total);
        $serverTotal = intval($order->grand_total);
        if ($serverTotal !== $clientTotal) {
            throw new BusinessRuleViolationException('Gagal menyimpan transaksi, data tidak sinkron, coba refresh halaman!');
        }
    }

    private function ensureOrderIsEditable(SalesOrder $order)
    {
        if ($order->status != SalesOrder::Status_Draft) {
            throw new BusinessRuleViolationException('Order sudah tidak dapat diubah.');
        }
    }

    private function processSalesOrderStockOut(SalesOrder $order)
    {
        foreach ($order->details as $detail) {
            $productType = $detail->product->type;
            // TODO: Skip tipe produk tertentu
            if (
                $productType == Product::Type_NonStocked
                || $productType == Product::Type_Service
            ) {
                continue;
            }

            $quantity = $detail->quantity;
            $product = $detail->product;

            StockMovement::create([
                'product_id'      => $detail->product_id,
                'product_name'    => $detail->product_name,
                'uom'             => $detail->product_uom,
                'ref_id'          => $detail->id,
                'ref_type'        => StockMovement::RefType_SalesOrderDetail,
                'quantity'        => -$quantity,
                'quantity_before' => $product->stock,
                'quantity_after'  => $product->stock - $quantity,
                'notes'           => "Transaksi penjualan #$order->code",
            ]);

            Product::where('id', $detail->product_id)->decrement('stock', $quantity);
        }
    }

    private function reverseStock(SalesOrder $order)
    {
        foreach ($order->details as $detail) {
            Product::where('id', $detail->product_id)->increment('stock', $detail->quantity);

            StockMovement::where('ref_type', StockMovement::RefType_SalesOrderDetail)
                ->where('ref_id', $detail->id)
                ->delete();
        }
    }
}
