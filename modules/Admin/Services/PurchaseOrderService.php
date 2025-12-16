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
use App\Models\PurchaseOrderDetail;
use App\Models\PurchaseOrderReturn;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\SupplierLedger;
use App\Models\UserActivityLog;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
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
        $order = PurchaseOrder::with([
            'supplier',
            'details',
            'payments',
            'details.product',
            'payments.account'
        ])
            ->findOrFail($id);
        return $order;
    }

    public function findOrderDetailOrFail(int $id): PurchaseOrderDetail
    {
        return PurchaseOrderDetail::findOrFail($id);
    }

    public function createOrder(): PurchaseOrder
    {
        $item = PurchaseOrder::where('status', PurchaseOrder::Status_Draft)
            ->where('created_by', Auth::user()->id)
            ->where('grand_total', 0)
            ->orderBy('id', 'desc')
            ->first();

        if ($item) {
            return $item;
        }

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
                "Order pembelian $item->code telah dibuat.",
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

        $supplier = !empty($data['supplier_id']) ? Supplier::findOrFail($data['supplier_id']) : null;
        $item->supplier_id      = $supplier?->id;
        $item->supplier_code    = $supplier?->code;
        $item->supplier_name    = $supplier?->name;
        $item->supplier_phone   = $supplier?->phone_1;
        $item->supplier_address = $supplier?->address;

        $item->notes = $data['notes'];
        $item->datetime = $data['datetime'];
        $item->total_discount = $data['total_discount'] ?? 0;

        return DB::transaction(function () use ($item) {
            $item->updateGrandTotal();
            $item->save();
            // kita tidak mencatat log dan lacak version di penyimpanan ini
            // untuk menghemat ruang dan meminimalisir interaksi database
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
                "Order pembelian $item->code telah dibatalkan.",
                [
                    'data' => $item->toArray(),
                    'formatter' => 'puchase-order',
                ]
            );

            return $item;
        });
    }

    private function validateTotal(PurchaseOrder $order, $client_total)
    {
        $order->updateGrandTotal();
        $clientTotal = intval($client_total);
        $serverTotal = intval($order->grand_total);
        if ($serverTotal !== $clientTotal) {
            throw new BusinessRuleViolationException('Gagal menyimpan transaksi, total tidak sinkron. Coba refresh halaman!');
        }
    }

    private function processPurchaseOrderStockIn(PurchaseOrder $order)
    {
        foreach ($order->details as $detail) {
            $product = Product::where('id', $detail->product_id)
                ->lockForUpdate()
                ->first();

            $productType = $product->type;

            // 2. Skip Tipe Produk Non-Stock
            if (
                $productType == Product::Type_NonStocked
                || $productType == Product::Type_Service
            ) {
                continue;
            }

            // 3. HITUNG KUANTITAS DALAM SATUAN DASAR
            // Rumus: Qty Transaksi * Rate Konversi
            $rate = $detail->conversion_rate > 0 ? $detail->conversion_rate : 1; // fallback ke 1, gimana kalau fallback ke data di UOM???
            $qtyTransaction = $detail->quantity;
            $qtyBase = $qtyTransaction * $rate; // Stock In nilainya Positif

            // 4. HITUNG COST DALAM SATUAN DASAR
            $baseCost = ($rate > 0) ? ($detail->cost / $rate) : $detail->cost;

            // 5. AMBIL STOK 'REAL-TIME' UNTUK SNAPSHOT
            $stockBefore = $product->stock;
            $stockAfter  = $stockBefore + $qtyBase;

            // 6. UPDATE STOCK MOVEMENT
            // Kita sesuaikan array ini agar dikirim ke service,
            // tapi strukturnya mengikuti standar SalesOrder (Base UOM + Audit Trail)
            $this->stockMovementService->processStockIn([
                // Header Dokumen
                'parent_id'       => $order->id,
                'parent_ref_type' => StockMovement::ParentRefType_PurchaseOrder,
                'document_code'   => $order->code,
                'document_datetime' => $order->datetime,

                // Pihak Terkait (Supplier)
                'party_id'        => $order->supplier_id,
                'party_type'      => 'supplier',
                'party_code'      => $order->supplier_code,
                'party_name'      => $order->supplier_name,

                // Info Produk
                'product_id'      => $detail->product_id,
                'product_name'    => $detail->product_name,

                // PENTING: UOM di StockMovement WAJIB Satuan Dasar Produk
                'uom'             => $product->uom,

                'ref_id'          => $detail->id,
                'ref_type'        => StockMovement::RefType_PurchaseOrderDetail,

                // PENTING: Quantity yang menambah stok adalah Quantity Base
                'quantity'        => $qtyBase,

                // Audit Trail (Info Transaksi Asli)
                'user_input_qty'  => $qtyTransaction,
                'user_input_uom'  => $detail->product_uom,
                'conversion_rate' => $rate,

                // Snapshot Stok
                'quantity_before' => $stockBefore,
                'quantity_after'  => $stockAfter,

                'notes'           => "Pembelian #$order->code ($qtyTransaction {$detail->product_uom})",
            ]);

            // 7. Update Master Product (Stok & Cost Baru)
            $product->stock = $stockAfter;
            $product->cost  = $baseCost; // Update modal dasar terbaru
            $product->save();
        }
    }

    private function processPurchaseOrderStockOut(PurchaseOrder $order)
    {
        // Fungsi ini dipanggil saat PEMBATALAN (Void/Delete) Purchase Order
        // Logicnya: Kurangi Stok (Reverse Stock In) & Hapus Mutasi

        foreach ($order->details as $detail) {
            // 1. HITUNG QTY BASE
            $rate = $detail->conversion_rate > 0 ? $detail->conversion_rate : 1;
            $qtyBase = $detail->quantity * $rate;

            // 2. KEMBALIKAN STOK (KURANGI) DALAM SATUAN DASAR
            // Karena ini reverse dari pembelian (Stock In), maka kita DECREMENT
            Product::where('id', $detail->product_id)->decrement('stock', $qtyBase);

            // Catatan: Biasanya saat reverse PO, kita tidak mengembalikan 'cost' ke harga lama
            // karena cost bersifat fluktuatif (moving average atau last price).
            // Jadi cukup stoknya saja yang ditarik.

            // 3. HAPUS MUTASI
            $this->stockMovementService->deleteByRef(
                $detail->id,
                StockMovement::RefType_PurchaseOrderDetail
            );
        }
    }

    public function closeOrder(PurchaseOrder $order, array $data)
    {
        $this->ensureOrderIsEditable($order);

        DB::transaction(function () use ($order, $data) {
            $this->validateTotal($order, $data['total'] ?? 0);

            $this->paymentService->addPayments($order, $data['payments'] ?? [], false);

            // simpan order
            $order->datetime = now(); // hard coded ke waktu sekarang
            $order->due_date = $data['due_date'] ?? null;
            $order->delivery_status = PurchaseOrder::DeliveryStatus_PickedUp;
            $order->status = PurchaseOrder::Status_Closed;
            $order->updateTotals();
            $order->save();

            $supplier = $order->supplier;
            if ($supplier && $order->balance != 0) {
                app(SupplierLedgerService::class)->save([
                    'supplier_id' => $order->supplier_id,
                    'datetime'    => now(),
                    'type'        => SupplierLedger::Type_Bill,
                    'amount'      => abs($order->balance),
                    'notes'       => 'Tagihan transaksi pembelian #' . $order->code,
                    'ref_type'    => SupplierLedger::RefType_PurchaseOrder,
                    'ref_id'      => $order->id,
                ]);
            }

            // catat stok
            $this->processPurchaseOrderStockIn($order);

            // bikin versioning
            $this->documentVersionService->createVersion($order);

            // log aktifitas
            $this->userActivityLogService->log(
                UserActivityLog::Category_PurchaseOrder,
                UserActivityLog::Name_PurchaseOrder_Close,
                "Order pembelian $order->code telah ditutup.",
                [
                    'data' => $order->toArray(),
                    'formatter' => 'puchase-order',
                ]
            );
        });
    }

    public function deleteOrder(PurchaseOrder $order): PurchaseOrder
    {
        if (PurchaseOrderReturn::where('purchase_order_id', $order->id)->exists()) {
            throw new BusinessRuleViolationException('Tidak dapat dihapus karena ada transaksi retur!');
        }

        return DB::transaction(function () use ($order) {
            if ($order->status == PurchaseOrder::Status_Closed) {
                $this->processPurchaseOrderStockOut($order);

                foreach ($order->payments as $payment) {
                    $this->paymentService->deletePayment($payment, false);
                }
            }
            $code = $order->code;

            $order->delete();

            if ($order->status == PurchaseOrder::Status_Closed && $order->supplier_id) {
                app(SupplierLedgerService::class)->deleteByRef(
                    SupplierLedger::RefType_PurchaseOrder,
                    $order->id
                );
            }

            $this->documentVersionService->createDeletedVersion($order);

            $this->userActivityLogService->log(
                UserActivityLog::Category_PurchaseOrder,
                UserActivityLog::Name_PurchaseOrder_Delete,
                "Order pembelian $code telah dihapus.",
                [
                    'data' => $order->toArray(),
                    'formatter' => 'puchase-order',
                ]
            );

            return $order;
        });
    }

    public function getData(array $options): LengthAwarePaginator
    {
        $filter = $options['filter'];

        $q = PurchaseOrder::query();

        if (!empty($filter['search'])) {
            $q->where(function ($q) use ($filter) {
                $q->where('code', 'like', "%" . $filter['search'] . "%");
                $q->orWhere('notes', 'like', '%' . $filter['search'] . '%');
                $q->orWhere('supplier_code', 'like', '%' . $filter['search'] . '%');
                $q->orWhere('supplier_name', 'like', '%' . $filter['search'] . '%');
                $q->orWhereHas('details.product', function ($q) use ($filter) {
                    $q->where('name', 'like', "%" . $filter['search'] . "%");
                });
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

        if (!empty($filter['start_date'])) {
            $q->where('datetime', '>=', $filter['start_date']);
        }

        if (!empty($filter['end_date'])) {
            $q->where('datetime', '<=', $filter['end_date']);
        }

        if (!empty($filter['supplier_id']) && $filter['supplier_id'] !== 'all') {
            $q->where('supplier_id', $filter['supplier_id']);
        }

        $q->orderBy($options['order_by'], $options['order_type']);

        return $q->paginate($options['per_page']);
    }

    private function ensureOrderIsEditable(PurchaseOrder $order)
    {
        if ($order->status != PurchaseOrder::Status_Draft) {
            throw new BusinessRuleViolationException('Order sudah tidak dapat diubah.');
        }
    }
}
