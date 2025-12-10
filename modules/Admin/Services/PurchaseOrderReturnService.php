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
use App\Models\SupplierLedger;
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
        protected PurchaseOrderService $purchaseOrderService,
    ) {}

    // OK
    public function getData(array $options)
    {
        $orderBy = $options['order_by'];
        $orderType = $options['order_type'];
        $filter = $options['filter'];

        $q = PurchaseOrderReturn::with(['purchaseOrder:id,code']);

        if (!empty($filter['search'])) {
            $q->where(function ($q) use ($filter) {
                $q->where('code', 'like', "%" . $filter['search'] . "%");
                $q->orWhere('notes', 'like', '%' . $filter['search'] . '%');
                $q->orWhere('supplier_code', 'like', '%' . $filter['search'] . '%');
                $q->orWhere('supplier_name', 'like', '%' . $filter['search'] . '%');
                $q->orWhere('supplier_phone', 'like', '%' . $filter['search'] . '%');
                $q->orWhere('supplier_address', 'like', '%' . $filter['search'] . '%');

                $q->orWhereHas('details.product', function ($q) use ($filter) {
                    $q->where('name', 'like', "%" . $filter['search'] . "%");
                });
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

        if (!empty($filter['start_date'])) {
            $q->where('datetime', '>=', $filter['start_date']);
        }

        if (!empty($filter['end_date'])) {
            $q->where('datetime', '<=', $filter['end_date']);
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
            'supplier_code' => $order->supplier_code,
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
    public function editOrder(PurchaseOrderReturn $return): PurchaseOrderReturn
    {
        $this->ensureOrderIsEditable($return);

        return $return;
    }

    // OK
    public function updateOrder(PurchaseOrderReturn $item, array $data): PurchaseOrderReturn
    {
        $this->ensureOrderIsEditable($item);

        $item->notes = $data['notes'];
        $item->datetime = $data['datetime'];
        $item->total_discount = $data['total_discount'] ?? 0;

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
            'purchaseOrder',
            'details',
            'payments',
            'payments.account',
        ])
            ->findOrFail($id);
    }

    public function closeOrderReturn(PurchaseOrderReturn $return, array $data)
    {
        $this->ensureOrderIsEditable($return);

        DB::transaction(function () use ($return, $data) {
            $return->status = PurchaseOrderReturn::Status_Closed;
            $return->datetime = now(); // hard coded ke waktu sekarang
            $return->updateGrandTotal();
            $return->updateBalanceAndStatus();
            $return->save();

            /**
             * @var PurchaseOrder
             */
            $order = $return->purchaseOrder;
            $balanceDelta = 0;
            if ($order) {
                $oldBalance = $order->balance;
                $order->updateTotals();
                $order->save();
                $balanceDelta = $order->balance - $oldBalance;
            }

            $supplier = $return->supplier;
            if ($supplier && $balanceDelta != 0) {
                app(SupplierLedgerService::class)->save([
                    'supplier_id' => $supplier->id,
                    'datetime'    => now(),
                    'type'        => SupplierLedger::Type_DebitNote,
                    'amount'      => abs($balanceDelta),
                    'notes'       => 'Retur transaksi pembelian #' . $return->code,
                    'ref_type'    => SupplierLedger::RefType_PurchaseOrderReturn,
                    'ref_id'      => $return->id,
                ]);
            }

            // kita harus update lagi karena sebelumnya order belum diupdate
            $return->updateBalanceAndStatus();
            $return->save();

            $this->processPurchaseOrderReturnStockOut($return);
        });
    }

    // OK
    public function deleteOrderReturn(PurchaseOrderReturn $return)
    {
        DB::transaction(function () use ($return) {
            if ($return->status == PurchaseOrderReturn::Status_Closed) {
                $this->reverseStock($return);
                $this->refundService->deleteRefunds($return);

                if ($return->supplier_id) {
                    app(SupplierLedgerService::class)->deleteByRef(
                        SupplierLedger::RefType_PurchaseOrderReturn,
                        $return->id
                    );
                }
            }

            $return->delete();

            if ($return->status == PurchaseOrderReturn::Status_Closed) {
                /**
                 * @var PurchaseOrder
                 */
                $order = $return->purchaseOrder;
                if ($order) {
                    $order->updateTotals();
                    $order->save();
                }
            }
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
    private function processPurchaseOrderReturnStockOut(PurchaseOrderReturn $return)
    {
        foreach ($return->details as $detail) {
            $productType = $detail->product->type;
            if (
                $productType == Product::Type_NonStocked
                || $productType == Product::Type_Service
            ) {
                continue;
            }

            $quantity = $detail->quantity;

            StockMovement::create([
                'parent_id'         => $return->id,
                'parent_ref_type'   => StockMovement::ParentRefType_PurchaseOrderReturn,
                'document_code'     => $return->code,
                'document_datetime' => $return->datetime,
                'party_id'          => $return->supplier_id,
                'party_type'        => 'supplier',
                'party_code'        => $return->supplier_code,
                'party_name'        => $return->supplier_name,

                'product_id'      => $detail->product_id,
                'product_name'    => $detail->product_name,
                'uom'             => $detail->product_uom,
                'ref_id'          => $detail->id,
                'ref_type'        => StockMovement::RefType_PurchaseOrderReturnDetail,
                'quantity'        => -$quantity,
                'quantity_before' => $detail->product->stock,
                'quantity_after'  => $detail->product->stock - $quantity,
                'notes'           => "Retur pembelian #$return->code",
            ]);

            Product::where('id', $detail->product_id)->decrement('stock', $quantity);
        }
    }

    // OK
    private function reverseStock(PurchaseOrderReturn $return)
    {
        foreach ($return->details as $detail) {
            Product::where('id', $detail->product_id)->increment('stock', $detail->quantity);

            StockMovement::where('ref_type', StockMovement::RefType_PurchaseOrderReturnDetail)
                ->where('ref_id', $detail->id)
                ->delete();
        }
    }
}
