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
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderReturn;
use App\Models\StockMovement;
use App\Models\UserActivityLog;
use Illuminate\Support\Facades\DB;

class PurchaseOrderReturnService
{
    public function __construct(
        protected PurchaseOrderReturnRefundService $refundService,
        protected UserActivityLogService $userActivityLogService,
        protected DocumentVersionService $documentVersionService,
        protected FinanceTransactionService $financeTransactionService,
        protected StockMovementService $stockMovementService,
        protected ProductService $productService,
        protected SupplierService $supplierService,
        protected CashierSessionService $cashierSessionService,
    ) {}

    // OK
    public function getData(array $options)
    {
        $orderBy = $options['order_by'];
        $orderType = $options['order_type'];
        $filter = $options['filter'];

        $q = PurchaseOrderReturn::with(['purchaseOrder', 'supplier', 'details', 'details.product']);

        if (!empty($filter['search'])) {
            $q->where(function ($q) use ($filter) {
                $q->orWhere('code', 'like', "%" . $filter['search'] . "%");
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

        if (!empty($filter['supplier_id']) && $filter['supplier_id'] !== 'all') {
            $q->where('supplier_id', $filter['supplier_id']);
        }

        if (!empty($filter['cashier_session_id']) && $filter['cashier_session_id'] !== 'all') {
            $q->where('cashier_session_id', $filter['cashier_session_id']);
        }

        // $q->select(['id', 'total_price', 'datetime', 'status', 'payment_status', 'delivery_status'])
        $q->orderBy($orderBy, $orderType);

        return $q->paginate($options['per_page']);
    }

    public function createOrderReturn(PurchaseOrder $order): PurchaseOrderReturn
    {
        if ($order->status !== PurchaseOrder::Status_Closed) {
            throw new BusinessRuleViolationException('Transaksi belum selesai tidak dapat diretur!');
        }

        $item = new PurchaseOrderReturn([
            'purchase_order_id' => $order->id,
            'supplier_id' => $order->supplier_id,
            'supplier_name' => $order->supplier_name,
            'supplier_phone' => $order->supplier_phone,
            'supplier_address' => $order->supplier_address,
            'status' => PurchaseOrderReturn::Status_Draft,
            'refund_status' => PurchaseOrderReturn::RefundStatus_Pending,
            'grand_total' => $order->grand_total,
            'datetime' => now(),
        ]);
        $item->save();
        return $item;
    }

    // OK
    public function findOrderOrFail(int $id, $relations = ['purchaseOrder', 'details', 'supplier'], $cols = "*"): PurchaseOrderReturn
    {
        return PurchaseOrderReturn::with($relations)->findOrFail($id, $cols);
    }

    // OK
    public function editOrder(PurchaseOrderReturn $order): PurchaseOrderReturn
    {
        $this->ensureOrderIsEditable($order);

        return $order;
    }

    // OK
    public function updateOrder(PurchaseOrderReturn $item, array $data): PurchaseOrderReturn
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
    public function cancelOrder(PurchaseOrderReturn $orderReturn): PurchaseOrderReturn
    {
        $this->ensureOrderIsEditable($orderReturn);

        $orderReturn->status = PurchaseOrderReturn::Status_Canceled;

        return DB::transaction(function () use ($orderReturn) {
            $orderReturn->save();

            $this->documentVersionService->createVersion($orderReturn);

            $this->userActivityLogService->log(
                UserActivityLog::Category_PurchaseOrderReturn,
                UserActivityLog::Name_PurchaseOrderReturn_Cancel,
                "Retur pembelian $orderReturn->code telah dibatalkan.",
                [
                    'data' => $orderReturn->toArray(),
                    'formatter' => 'purchase-order-return',
                ]
            );

            return $orderReturn;
        });
    }

    public function getOrderWithDetails($id): PurchaseOrderReturn
    {
        return PurchaseOrderReturn::with([
            'supplier',
            'details',
            'refunds',
            'refunds.account',
        ])
            ->findOrFail($id);
    }

    public function closeOrderReturn(PurchaseOrderReturn $order, array $data)
    {
        $this->ensureOrderIsEditable($order);

        DB::transaction(function () use ($order, $data) {
            $order->status = PurchaseOrderReturn::Status_Closed;

            if ($order->supplier_id && $order->purchaseOrder->remaining_debt > 0) {
                $order->purchaseOrder->remaining_debt -= $order->remaining_refund;
                $order->purchaseOrder->save();

                $order->supplier->balance += $order->remaining_refund;
                $order->supplier->save();
            } else {
                // untuk cash dan transaksi lunas gak perlu dihandle, langsung kasih refund
                $order->remaining_refund = $order->grand_total;
            }

            $order->save();
            $this->processPurchaseOrderReturnStockOut($order);
        });
    }

    // IN PROGRESS
    public function deleteOrderReturn(PurchaseOrderReturn $order)
    {
        DB::transaction(function () use ($order) {
            if ($order->status == PurchaseOrderReturn::Status_Closed) {
                $this->reverseStock($order);
                $this->refundService->deleteRefunds($order);
            }
            $order->delete();
        });
    }

    // OK
    private function ensureOrderIsEditable(PurchaseOrderReturn $orderReturn)
    {
        if ($orderReturn->status != PurchaseOrderReturn::Status_Draft) {
            throw new BusinessRuleViolationException('Order sudah tidak dapat diubah.');
        }
    }

    // OK
    private function processPurchaseOrderReturnStockOut(PurchaseOrderReturn $order)
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
                'ref_type'        => StockMovement::RefType_PurchaseOrderReturnDetail,
                'quantity'        => -$quantity,
                'quantity_before' => $detail->product->stock,
                'quantity_after'  => $detail->product->stock - $quantity,
                'notes'           => "Retur pembelian #$order->code",
            ]);

            Product::where('id', $detail->product_id)->decrement('stock', $quantity);
        }
    }

    // OK
    private function reverseStock(PurchaseOrderReturn $order)
    {
        foreach ($order->details as $detail) {
            Product::where('id', $detail->product_id)->decrement('stock', $detail->quantity);

            StockMovement::where('ref_type', StockMovement::RefType_PurchaseOrderReturnDetail)
                ->where('ref_id', $detail->id)
                ->delete();
        }
    }
}
