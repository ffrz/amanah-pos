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

namespace Modules\Admin\Http\Controllers;

use App\Helpers\JsonResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\Customer;
use App\Models\CustomerWalletTransaction;
use App\Models\FinanceAccount;
use App\Models\FinanceTransaction;
use App\Models\Product;
use App\Models\PurchaseOrderDetail;
use App\Models\PurchaseOrderPayment;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Services\CashierSessionService;
use App\Services\FinanceTransactionService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    protected $financeTransactionService;
    public function __construct(
        FinanceTransactionService $financeTransactionService,
    ) {
        $this->financeTransactionService = $financeTransactionService;
    }

    public function index()
    {
        return inertia('purchase-order/Index');
    }

    public function data(Request $request)
    {
        $orderBy = $request->get('order_by', 'datetime');
        $orderType = $request->get('order_type', 'desc');
        $filter = $request->get('filter', []);

        $q = PurchaseOrder::with(['supplier', 'details', 'details.product']);

        if (!empty($filter['search'])) {
            $q->where(function ($q) use ($filter) {
                $q->orWhere('notes', 'like', '%' . $filter['search'] . '%');
                $q->orWhere('supplier_username', 'like', '%' . $filter['search'] . '%');
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
        $q->orderBy($orderBy, $orderType);

        $items = $q->paginate($request->get('per_page', 10))->withQueryString();

        return response()->json($items);
    }

    public function editor($id = 0)
    {
        if (!$id) {
            $item = new PurchaseOrder([
                'type' => PurchaseOrder::Type_Pickup,
                'datetime' => Carbon::now(),
                'status' => PurchaseOrder::Status_Draft,
                'payment_status' => PurchaseOrder::PaymentStatus_Unpaid,
                'delivery_status' => PurchaseOrder::DeliveryStatus_ReadyForPickUp,
            ]);
            $item->save();
            return redirect(route('admin.purchase-order.edit', $item->id));
        }

        $item = PurchaseOrder::with(['details', 'supplier'])->findOrFail($id);

        // FIX ME: Jika mau aktifkan reopen order, tangani bagian ini
        if ($item->status !== PurchaseOrder::Status_Draft) {
            // TODO: untuk reopen order, harusnya ada nilai yang dikirim client misal action=reopen
            // agar lebih eksplisit, untuk MVP reopen tidak support
            abort(403, 'Transaksi sudah tidak dapat diubah');
            return;

            // reopen order jika sudah bukan draft
            if ($item->status === PurchaseOrder::Status_Closed) {
                $item->status = PurchaseOrder::Status_Draft;
                // TODO: kembalikan stok jika sudah ditutup, atau tolak kalau tidak boleh reopen
                $item->save();
            } else if ($item->status === PurchaseOrder::Status_Canceled) {
                $item->status = PurchaseOrder::Status_Draft;
                $item->save();
            }
        }

        return inertia('purchase-order/Editor', [
            'data' => $item,
            'accounts' => $this->getFinanceAccounts(),
        ]);
    }

    public function update(Request $request)
    {
        $item = PurchaseOrder::find($request->post('id'));

        if ($item->status != PurchaseOrder::Status_Draft) {
            return JsonResponseHelper::error('Order tidak dapat diubah.', 403);
        }

        $supplier = Supplier::find($request->post('supplier_id'));

        // Nilai awal customer info dari saat diganti customer
        $item->supplier_id = $supplier ? $supplier->id : null;
        $item->supplier_name = $supplier?->name;
        $item->supplier_phone = $supplier?->phone;
        $item->supplier_address = $supplier?->address;

        $item->notes = $request->post('notes', '');
        $item->datetime = $request->post('datetime', Carbon::now());

        // FIXME: Saat ini memang belum dibutuhkan karena gak bisa diubah dari client
        // $item->status = $request->post('status', PurchaseOrder::Status_Draft);
        // $item->payment_status = $request->post('payment_status', PurchaseOrder::PaymentStatus_Unpaid);
        // $item->delivery_status = $request->post('delivery_status', PurchaseOrder::DeliveryStatus_ReadyForPickUp);

        $item->save();

        return JsonResponseHelper::success($item, 'Order telah diperbarui');
    }

    public function cancel($id)
    {
        $item = PurchaseOrder::findOrFail($id);
        if ($item->status == PurchaseOrder::Status_Draft) {
            $item->status = PurchaseOrder::Status_Canceled;
            $item->save();
            return JsonResponseHelper::success(
                ['id' => $item->id],
                "Transaksi #$item->formatted_id telah dibatalkan."
            );
        }
        return JsonResponseHelper::error('Status order ini tidak dapat diubah.');
    }

    public function delete($id)
    {
        $order = PurchaseOrder::with([
            'details',
            'payments',
            'details.product',
            'payments.account'
        ])->findOrFail($id);

        try {
            DB::beginTransaction();
            if ($order->status == PurchaseOrder::Status_Closed) {
                // refund stok
                foreach ($order->details as $detail) {
                    $product = $detail->product;
                    $product->stock -= abs($detail->quantity);
                    $product->save();

                    DB::delete(
                        'DELETE FROM stock_movements WHERE ref_type = ? AND ref_id = ?',
                        [StockMovement::RefType_PurchaseOrderDetail, $detail->id]
                    );
                }

                foreach ($order->payments as $payment) {
                    if (
                        $payment->type == PurchaseOrderPayment::Type_Transfer
                        || $payment->type == PurchaseOrderPayment::Type_Cash
                    ) {
                        $this->financeTransactionService->reverseTransaction($payment->id, FinanceTransaction::RefType_PurchaseOrderPayment);
                    }

                    $payment->delete();
                }
            }

            $order->delete();

            DB::commit();

            return JsonResponseHelper::success($order, "Transaksi #$order->formatted_id telah dihapus.");
        } catch (\Throwable $ex) {
            DB::rollBack();
            return JsonResponseHelper::error("Gagal menghapus transaksi #$order->formatted_id.", 500, $ex);
        }
    }

    public function detail($id)
    {
        return inertia('purchase-order/Detail', [
            'data' => PurchaseOrder::with([
                'supplier',
                'details',
                'payments',
                'payments.account',
            ])
                ->findOrFail($id),
            'accounts' => $this->getFinanceAccounts()
        ]);
    }

    public function addItem(Request $request)
    {
        $order = PurchaseOrder::find($request->post('order_id'));
        if (!$order) {
            return JsonResponseHelper::error('Order tidak ditemukan');
        }

        if ($order->status !== PurchaseOrder::Status_Draft) {
            return JsonResponseHelper::error('Order sudah tidak dapat diubah.');
        }

        $product = null;
        $productCode = $request->post('product_code');
        $productId = $request->post('product_id');
        $merge = $request->post('merge', false);
        if ($productId) {
            $product = Product::find($request->post('product_id', 0));
        } elseif ($productCode) {
            // cari berdasarkan barcode
            $product = Product::where('barcode', '=', $productCode)->first();

            // kalo belum ketemu cari berdasarkan nama produk
            if (!$product) {
                $product = Product::where('name', '=', $productCode)->first();
            }
        }

        if (!$product) {
            return JsonResponseHelper::error('Produk tidak ditemukan');
        }

        $quantity = $request->post('qty', 0);
        $cost = $product->cost;
        if ($request->has('cost') && $request->post('cost') !== null) {
            $cost = $request->post('cost', 0);
        }

        $detail = null;
        if ($merge) {
            // kalo gabung cari rekaman yang sudah ada
            $detail = PurchaseOrderDetail::where('parent_id', '=', $order->id)
                ->where('product_id', '=', $product->id)
                ->get()
                ->first();
        }

        if ($detail) {
            $order->total  -= $detail->subtotal_cost;
            $detail->quantity += $quantity;
            $detail->subtotal_cost  = $detail->cost  * $detail->quantity;
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

        // update total dan subtotal baru
        $order->total  += $detail->subtotal_cost;
        $order->save();

        DB::commit();

        return JsonResponseHelper::success([
            'item' => $detail,
            'mergeItem' => $merge,
        ], 'Item telah ditambahkan');
    }

    public function updateItem(Request $request)
    {
        $detail = PurchaseOrderDetail::find($request->post('id'));
        if (!$detail) {
            return JsonResponseHelper::error('Detail Order tidak ditemukan');
        }

        $order = $detail->parent;

        if ($order->status !== PurchaseOrder::Status_Draft) {
            return JsonResponseHelper::error('Order sudah tidak dapat diubah.');
        }

        $order->total  -= $detail->subtotal_cost;

        $detail->quantity = $request->post('qty', 0);

        $cost = $request->post('cost');
        if ($cost !== null && $cost >= 0) {
            $detail->cost = $cost;
        }

        // perbarui subtotal
        $detail->subtotal_cost  = $detail->cost  * $detail->quantity;
        $detail->notes = $request->post('notes', '');

        DB::beginTransaction();
        $detail->save();

        // update total dan subtotal baru
        $order->total  += $detail->subtotal_cost;
        // FIX ME: ganti perhitungan jika sudah ada pajak dan diskon
        $order->grand_total = $order->total;
        $order->save();

        DB::commit();

        return JsonResponseHelper::success($detail, 'Item telah diperbarui.');
    }

    public function removeItem(Request $request)
    {
        $item = PurchaseOrderDetail::find($request->id);

        if (!$item) {
            return JsonResponseHelper::error('Item tidak ditemukan');
        }

        if ($item->parent->status !== PurchaseOrder::Status_Draft) {
            return JsonResponseHelper::error('Order selesai tidak dapat diubah!');
        }

        DB::beginTransaction();
        $order = $item->parent;
        $order->total -= $item->subtotal_cost;
        $order->save();
        $item->delete();
        DB::commit();
        return JsonResponseHelper::success($item, 'Item telah dihapus.');
    }

    public function close(Request $request)
    {
        $order = PurchaseOrder::with(['customer', 'details', 'details.product'])->find($request->post('id'));

        if (!$order) {
            return JsonResponseHelper::error('Item tidak ditemukan');
        }

        if ($order->status !== PurchaseOrder::Status_Draft) {
            return JsonResponseHelper::error('Order selesai tidak dapat diubah!');
        }

        try {
            DB::beginTransaction();

            // 1. Hitung total biaya dan harga di server
            // Anggap kita punya helper parseToInt untuk semua angka
            $total = $order->details->sum(function ($detail) {
                return round($detail->cost * $detail->quantity);
            });

            $order->total = $total;
            $order->grand_total = $order->total;

            // 2. Validasi grand total dengan input dari klien
            $clientTotal = intval($request->post('total', 0));
            $serverTotal = intval($order->grand_total);
            if ($serverTotal !== $clientTotal) {
                DB::rollBack();
                return JsonResponseHelper::error('Gagal menyimpan transaksi, coba refresh halaman!');
            }

            // 3. Simpan dan proses pembayaran
            $payments = $request->post('payments', []);
            $totalPaidAmount = 0;
            foreach ($payments as $inputPayment) {
                if (!isset($inputPayment['id'])) {
                    DB::rollBack();
                    throw new Exception('Invalid input payment format!');
                }

                $amount = intval($inputPayment['amount']);
                $totalPaidAmount += $amount;

                $accountId = null;
                $type = null;

                if (intval($inputPayment['id'])) {
                    $type = PurchaseOrderPayment::Type_Transfer;
                    $accountId = (int)$inputPayment['id'];
                }

                $payment = new PurchaseOrderPayment([
                    'order_id' => $order->id,
                    'finance_account_id' => $accountId,
                    'supplier_id' => $order->supplier?->id,
                    'type' => $type,
                    'amount' => $amount,
                ]);
                $payment->save();

                // Catat transaksi keuangan dan perbarui saldo
                if ($type === PurchaseOrderPayment::Type_Transfer || $type === PurchaseOrderPayment::Type_Cash) {
                    FinanceTransaction::create([
                        'account_id' => $accountId,
                        'datetime' => now(),
                        'type' => FinanceTransaction::Type_Expense,
                        'amount' => -$amount,
                        'ref_id' => $payment->id,
                        'ref_type' => FinanceTransaction::RefType_PurchaseOrderPayment,
                        'notes' => "Pembayaran transaksi #$order->formatted_id",
                    ]);
                    FinanceAccount::where('id', $accountId)->increment('balance', $amount);
                }
            }

            // 4. Update status pembayaran dan total yang dibayar
            $order->total_paid = $totalPaidAmount;
            $order->remaining_debt = max(0, $order->grand_total - $order->total_paid);

            if ($order->total_paid >= $order->grand_total) {
                $order->payment_status = PurchaseOrder::PaymentStatus_FullyPaid;
            } else if ($order->total_paid > 0) {
                $order->payment_status = PurchaseOrder::PaymentStatus_PartiallyPaid;
            } else {
                $order->payment_status = PurchaseOrder::PaymentStatus_Unpaid;
            }


            // FIXME: status langsung diambil tanpa harus seting di order
            $order->delivery_status = PurchaseOrder::DeliveryStatus_PickedUp;
            $order->status = PurchaseOrder::Status_Closed;
            $order->due_date = $request->post('due_date', null);
            $order->save();

            // 5. Perbarui stok produk secara massal
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
                StockMovement::create([
                    'product_id'      => $detail->product_id,
                    'product_name'    => $detail->product_name,
                    'uom'             => $detail->product_uom,
                    'ref_id'          => $detail->id,
                    'ref_type'        => StockMovement::RefType_PurchaseOrderDetail,
                    'quantity'        => $quantity,
                    'quantity_before' => $detail->product->stock,
                    'quantity_after'  => $detail->product->stock + $quantity,
                    'notes'           => "Transaksi penjualan #$order->formatted_id",
                ]);

                Product::where('id', $detail->product_id)->increment('stock', $quantity);
            }

            DB::commit();
        } catch (\Throwable $ex) {
            DB::rollBack();
            return JsonResponseHelper::error($ex->getMessage(), 500, $ex);
        }

        return JsonResponseHelper::success($order, "Order telah selesai.");
    }

    protected function getFinanceAccounts()
    {
        return FinanceAccount::where('active', '=', true)
            ->where(function ($query) {
                $query->where('type', '=', FinanceAccount::Type_Cash)
                    ->orWhere('type', '=', FinanceAccount::Type_Bank)
                    ->orWhere('type', '=', FinanceAccount::Type_PettyCash);
            })
            ->where('show_in_pos_payment', '=', true)
            ->orderBy('name')
            ->get();
    }

    public function addPayment(Request $request)
    {
        $order = PurchaseOrder::with(['supplier', 'details'])->find($request->post('order_id'));

        if (!$order) {
            return JsonResponseHelper::error('Pesanan penjualan tidak ditemukan.');
        }

        if ($order->remaining_debt <= 0) {
            return JsonResponseHelper::error('Pesanan ini sudah lunas.');
        }

        try {
            DB::beginTransaction();

            $payments = $request->post('payments', []);
            $totalPaidAmount = 0;

            foreach ($payments as $inputPayment) {
                if (!isset($inputPayment['id'])) {
                    throw new Exception('Invalid input payment format!');
                }

                $amount = intval($inputPayment['amount']);

                // Pastikan jumlah pembayaran tidak melebihi sisa tagihan
                if ($order->remaining_debt - $totalPaidAmount < $amount) {
                    throw new Exception('Jumlah pembayaran melebihi sisa tagihan.');
                }

                $totalPaidAmount += $amount;

                $accountId = null;
                $type = null;

                if (intval($inputPayment['id'])) {
                    $type = PurchaseOrderPayment::Type_Transfer;
                    $accountId = (int)$inputPayment['id'];
                }

                $payment = new PurchaseOrderPayment([
                    'order_id' => $order->id,
                    'finance_account_id' => $accountId,
                    'supplier_id' => $order->supplier?->id,
                    'type' => $type,
                    'amount' => $amount,
                ]);
                $payment->save();

                if ($type === PurchaseOrderPayment::Type_Transfer || $type === PurchaseOrderPayment::Type_Cash) {
                    FinanceTransaction::create([
                        'account_id' => $accountId,
                        'datetime' => now(),
                        'type' => FinanceTransaction::Type_Income,
                        'amount' => $amount,
                        'ref_id' => $payment->id,
                        'ref_type' => FinanceTransaction::RefType_PurchaseOrderPayment,
                        'notes' => "Pembayaran transaksi #$order->formatted_id",
                    ]);
                    FinanceAccount::where('id', $accountId)->increment('balance', $amount);
                }
            }

            $order->total_paid += $totalPaidAmount;
            $order->remaining_debt = max(0, $order->grand_total - $order->total_paid);

            if ($order->total_paid >= $order->grand_total) {
                $order->payment_status = PurchaseOrder::PaymentStatus_FullyPaid;
            } else if ($order->total_paid > 0) {
                $order->payment_status = PurchaseOrder::PaymentStatus_PartiallyPaid;
            } else {
                $order->payment_status = PurchaseOrder::PaymentStatus_Unpaid;
            }
            $order->save();

            DB::commit();

            return JsonResponseHelper::success($order, "Pembayaran berhasil dicatat.");
        } catch (\Throwable $ex) {
            DB::rollBack();
            return JsonResponseHelper::error($ex->getMessage(), 500, $ex);
        }
    }

    /**
     * Menangani penghapusan pembayaran untuk Sales Order.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deletePayment(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            // 1. Cari pembayaran berdasarkan ID
            $payment = PurchaseOrderPayment::with(['order', 'order.supplier'])->find($id);

            if (!$payment) {
                DB::rollBack();
                return JsonResponseHelper::error('Pembayaran tidak ditemukan.');
            }

            // 2. Pastikan order terkait dalam status 'closed'
            $order = $payment->order;
            if (!$order) {
                DB::rollBack();
                return JsonResponseHelper::error('Order terkait tidak ditemukan.');
            }

            if ($order->status !== PurchaseOrder::Status_Closed) {
                DB::rollBack();
                return JsonResponseHelper::error('Tidak dapat menghapus pembayaran dari order yang belum selesai.');
            }

            // 3. Batalkan transaksi keuangan terkait berdasarkan tipe pembayaran
            if ($payment->type === PurchaseOrderPayment::Type_Transfer || $payment->type === PurchaseOrderPayment::Type_Cash) {
                // Batalkan transaksi keuangan
                FinanceTransaction::where('ref_id', $payment->id)
                    ->where('ref_type', FinanceTransaction::RefType_PurchaseOrderPayment)
                    ->delete();

                // Kurangi saldo akun keuangan
                if ($payment->finance_account_id) {
                    FinanceAccount::where('id', $payment->finance_account_id)
                        ->decrement('balance', $payment->amount);
                }
            }

            // 4. Hapus entri pembayaran dari database
            $payment->delete();

            // 5. Perbarui status order
            $order->total_paid -= $payment->amount;
            $order->remaining_debt = max(0, $order->grand_total - $order->total_paid);

            if ($order->total_paid >= $order->grand_total) {
                $order->payment_status = PurchaseOrder::PaymentStatus_FullyPaid;
            } else if ($order->total_paid > 0) {
                $order->payment_status = PurchaseOrder::PaymentStatus_PartiallyPaid;
            } else {
                $order->payment_status = PurchaseOrder::PaymentStatus_Unpaid;
            }

            $order->save();

            DB::commit();

            return JsonResponseHelper::success($order, "Pembayaran berhasil dihapus.");
        } catch (\Throwable $ex) {
            DB::rollBack();
            return JsonResponseHelper::error($ex->getMessage(), 500, $ex);
        }
    }
}
