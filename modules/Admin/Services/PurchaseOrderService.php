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
use App\Models\FinanceAccount;
use App\Models\FinanceTransaction;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use App\Models\PurchaseOrderPayment;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\UserActivityLog;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class PurchaseOrderService
{
    public function __construct(
        protected PurchaseOrderPaymentService $paymentService,
        protected UserActivityLogService $userActivityLogService,
        protected DocumentVersionService $documentVersionService,
        protected FinanceTransactionService $financeTransactionService,
        protected StockMovementService $stockMovementService,
        protected ProductService $productService,
        protected SupplierService $supplierService,
    ) {}

    public function findOrderOrFail(int $id): PurchaseOrder
    {
        return PurchaseOrder::with([
            'supplier',
            'details',
            'payments',
            'details.product',
            'payments.account'
        ])->findOrFail($id);
    }

    public function findOrderDetailOrFail(int $id): PurchaseOrderDetail
    {
        return PurchaseOrderDetail::findOrFail($id);
    }

    public function createOrder(): PurchaseOrder
    {
        $item = new PurchaseOrder([
            'type' => PurchaseOrder::Type_Pickup,
            'datetime' => Carbon::now(),
            'status' => PurchaseOrder::Status_Draft,
            'payment_status' => PurchaseOrder::PaymentStatus_Unpaid,
            'delivery_status' => PurchaseOrder::DeliveryStatus_ReadyForPickUp,
        ]);

        return DB::transaction(function () use ($item) {
            $item->save();

            $this->documentVersionService->createVersion($item);

            $this->userActivityLogService->log(
                UserActivityLog::Category_PurchaseOrder,
                UserActivityLog::Name_PurchaseOrder_Create,
                "Order pembelian $item->formatted_id telah dibuat.",
                [
                    'data' => $item->toArray(),
                    'formatter' => 'puchase-order',
                ]
            );

            return $item;
        });
    }

    public function editOrder(int $id): PurchaseOrder
    {
        $item = $this->findOrderOrFail($id);

        $this->ensureOrderIsEditable($item);

        return $item;
    }

    public function updateOrder(PurchaseOrder $item, array $data): PurchaseOrder
    {
        $this->ensureOrderIsEditable($item);

        $supplier = $data['supplier_id'] ? Supplier::findOrFail($data['supplier_id']) : null;

        // WARNING: logika ini perlu diperbarui jika mendukung customization di frontend
        // saat ini info supplier selalu diperbarui dari data supplier
        // karena frontend tidak mendukung customization
        if ($supplier) {
            $item->supplier_id      = $supplier->id;
            $item->supplier_name    = $supplier->name;
            $item->supplier_phone   = $supplier->phone;
            $item->supplier_address = $supplier->address;
        }

        $item->notes = $data['notes'];
        $item->datetime = $data['datetime'];

        return DB::transaction(function () use ($item) {
            $item->save();
            // kita tidak penyimpanan ini untuk menghemat ruang dan meminimalisir interaksi database
            return $item;
        });
    }

    public function cancelOrder(PurchaseOrder $item): PurchaseOrder
    {
        $this->ensureOrderIsEditable($item);

        $item->status = PurchaseOrder::Status_Canceled;

        return DB::transaction(function () use ($item) {
            $item->save();

            $this->documentVersionService->createVersion($item);

            $this->userActivityLogService->log(
                UserActivityLog::Category_PurchaseOrder,
                UserActivityLog::Name_PurchaseOrder_Cancel,
                "Order pembelian $item->formatted_id telah dibatalkan.",
                [
                    'data' => $item->toArray(),
                    'formatter' => 'puchase-order',
                ]
            );

            return $item;
        });
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

    private function processPurchaseOrderStockIn(PurchaseOrder $order)
    {
        // 5. Perbarui stok produk secara massal
        foreach ($order->details as $detail) {
            $productType = $detail->product->type;
            // TODO: Tambahkan ke sini untuk skip tipe produk yang stoknya tidak dilacak
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
                'ref_type'        => StockMovement::RefType_PurchaseOrderDetail,
                'quantity'        => $quantity,
                'quantity_before' => $detail->product->stock,
                'quantity_after'  => $detail->product->stock + $quantity,
                'notes'           => "Transaksi pembelian #$order->formatted_id",
            ]);

            $product = Product::where('id', $detail->product_id)->lockForUpdate()->firstOrFail();
            $product->stock += $quantity; // increment
            $product->cost = $detail->cost;
            $product->save();
        }
    }

    public function closeOrder(PurchaseOrder $order, array $data)
    {
        DB::transaction(function () use ($order, $data) {
            $this->updateTotalAndValidateClientTotal($order, $data['total'] ?? 0);

            $this->paymentService->addPayments($order, $data['payments'] ?? []);

            // TODO: Perbarui jika dibutuhkan karena sat ini gak support delivery order,
            // jadi status langsung diambil tanpa harus seting di order
            $order->delivery_status = PurchaseOrder::DeliveryStatus_PickedUp;
            $order->status = PurchaseOrder::Status_Closed;
            $order->due_date = $data['due_date'] ?? null;
            $order->save();

            $this->processPurchaseOrderStockIn($order);

            $this->documentVersionService->createVersion($order);

            $this->userActivityLogService->log(
                UserActivityLog::Category_PurchaseOrder,
                UserActivityLog::Name_PurchaseOrder_Close,
                "Order pembelian $order->formatted_id telah ditutup.",
                [
                    'data' => $order->toArray(),
                    'formatter' => 'puchase-order',
                ]
            );
        });
    }

    public function deleteOrder(PurchaseOrder $item): PurchaseOrder
    {
        return DB::transaction(function () use ($item) {
            if ($item->status == PurchaseOrder::Status_Closed) {
                // refund stok
                foreach ($item->details as $detail) {
                    $this->productService->addToStock($detail->product, -abs($detail->quantity));
                    $this->stockMovementService->deleteByRef($detail->id, StockMovement::RefType_PurchaseOrderDetail);
                }

                // refund saldo
                foreach ($item->payments as $payment) {
                    if (
                        $payment->type == PurchaseOrderPayment::Type_Transfer
                        || $payment->type == PurchaseOrderPayment::Type_Cash
                    ) {
                        $this->financeTransactionService->reverseTransaction(
                            $payment->id,
                            FinanceTransaction::RefType_PurchaseOrderPayment
                        );
                    }

                    $payment->delete();
                }

                // jika ada supplier kurangi utang di supplier
                if ($item->remaining_debt > 0 && $item->supplier_id) {
                    $this->supplierService->addToBalance($item->supplier, -abs($item->total_paid));
                }
            }

            $item->delete();

            $this->documentVersionService->createDeletedVersion($item);

            $this->userActivityLogService->log(
                UserActivityLog::Category_PurchaseOrder,
                UserActivityLog::Name_PurchaseOrder_Delete,
                "Order pembelian $item->formatted_id telah dihapus.",
                [
                    'data' => $item->toArray(),
                    'formatter' => 'puchase-order',
                ]
            );

            return $item;
        });
    }

    public function getData(array $options): LengthAwarePaginator
    {
        $filter = $options['filter'];

        $q = PurchaseOrder::with(['supplier', 'details', 'details.product']);

        if (!empty($filter['search'])) {
            $q->where(function ($q) use ($filter) {
                $q->orWhere('notes', 'like', '%' . $filter['search'] . '%');
                $q->orWhere('supplier_code', 'like', '%' . $filter['search'] . '%');
                $q->orWhere('supplier_name', 'like', '%' . $filter['search'] . '%');
                $q->orWhere('supplier_phone', 'like', '%' . $filter['search'] . '%');
                $q->orWhere('supplier_address', 'like', '%' . $filter['search'] . '%');
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

        if (!empty($filter['supplier_id']) && $filter['supplier_id'] !== 'all') {
            $q->where('supplier_id', $filter['supplier_id']);
        }

        // $q->select(['id', 'total_price', 'datetime', 'status', 'payment_status', 'delivery_status'])
        $q->orderBy($options['order_by'], $options['order_type']);

        return $q->paginate($options['per_page']);
    }

    public function addItem(PurchaseOrder $order, array $data, bool $merge = false)
    {
        $this->ensureOrderIsEditable($order);

        $product  = $this->productService->findProductByCodeOrId($data);
        $quantity = $data['qty'] ?? 0;
        $cost     = $data['cost'] ?? $product->cost;
        $detail   = null;

        // untuk opsi gabungkan item
        if ($merge) {
            $detail = PurchaseOrderDetail::where('parent_id', '=', $order->id)
                ->where('product_id', '=', $product->id)
                ->get()
                ->first();
        }

        if ($detail) {
            // increment quantity jika digabung
            $detail->addQuantity($quantity);
        } else {
            $detail = new PurchaseOrderDetail([
                'parent_id' => $order->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_barcode' => $product->barcode,
                'product_uom' => $product->uom,
                'quantity' => $quantity,
                'cost' => $cost,
                'subtotal_cost' => $quantity * $cost,
                'notes' => '',
            ]);
        }

        DB::beginTransaction();

        $detail->save();

        $order->updateTotals();
        $order->save();

        DB::commit();

        return $detail;
    }

    public function updateItem(PurchaseOrderDetail $detail, array $data)
    {
        /**
         * @var PurchaseOrder $order
         */
        $order = $detail->parent;

        $this->ensureOrderIsEditable($order);

        $detail->quantity = $data['qty'] ?? 0.;
        $detail->cost     = $data['cost'] ?? null;
        $detail->notes    = $data['notes'] ?? '';

        DB::beginTransaction();

        $detail->updateTotals();
        $detail->save();

        $order->updateTotals();
        $order->save();

        DB::commit();
    }

    public function removeItem(PurchaseOrderDetail $item)
    {
        /**
         * @var PurchaseOrder $order
         */
        $order = $item->parent;

        $this->ensureOrderIsEditable($order);

        DB::beginTransaction();

        $item->delete();

        $order->updateTotals();
        $order->save();

        DB::commit();
    }

    private function ensureOrderIsEditable(PurchaseOrder $order)
    {
        if ($order->status != PurchaseOrder::Status_Draft) {
            throw new BusinessRuleViolationException('Order sudah tidak dapat diubah.');
        }
    }
}
