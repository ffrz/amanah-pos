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

use App\Helpers\GeneratePdfHelper;
use App\Helpers\JsonResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\SalesOrder;
use App\Models\Customer;
use App\Models\CustomerWalletTransaction;
use App\Models\FinanceAccount;
use App\Models\FinanceTransaction;
use App\Models\Product;
use App\Models\SalesOrderDetail;
use App\Models\SalesOrderPayment;
use App\Models\Setting;
use App\Models\StockMovement;
use App\Services\CashierSessionService;
use App\Services\FinanceTransactionService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SalesOrderController extends Controller
{
    protected $financeTransactionService;
    public function __construct(
        FinanceTransactionService $financeTransactionService,
    ) {
        $this->financeTransactionService = $financeTransactionService;
    }

    public function index()
    {
        return inertia('sales-order/Index');
    }

    public function data(Request $request)
    {
        $orderBy = $request->get('order_by', 'datetime');
        $orderType = $request->get('order_type', 'desc');
        $filter = $request->get('filter', []);

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

        $items = $q->paginate($request->get('per_page', 10))->withQueryString();

        return response()->json($items);
    }

    public function editor($id = 0)
    {
        if (!$id) {
            $item = new SalesOrder([
                'type' => SalesOrder::Type_Pickup,
                'datetime' => Carbon::now(),
                'status' => SalesOrder::Status_Draft,
                'payment_status' => SalesOrder::PaymentStatus_Unpaid,
                'delivery_status' => SalesOrder::DeliveryStatus_ReadyForPickUp,
            ]);
            $item->cashier_id = Auth::user()->id;
            $item->save();
            return redirect(route('admin.sales-order.edit', $item->id));
        }

        $item = SalesOrder::with(['details', 'customer'])->findOrFail($id);


        // FIX ME: Jika mau aktifkan reopen order, tangani bagian ini
        if ($item->status !== SalesOrder::Status_Draft) {
            // TODO: untuk reopen order, harusnya ada nilai yang dikirim client misal action=reopen
            // agar lebih eksplisit, untuk MVP reopen tidak support
            abort(403, 'Transaksi sudah tidak dapat diubah');
            return;

            // reopen order jika sudah bukan draft
            if ($item->status === SalesOrder::Status_Closed) {
                $item->status = SalesOrder::Status_Draft;
                // TODO: kembalikan stok jika sudah ditutup, atau tolak kalau tidak boleh reopen
                $item->save();
            } else if ($item->status === SalesOrder::Status_Canceled) {
                $item->status = SalesOrder::Status_Draft;
                $item->save();
            }
        }

        return inertia('sales-order/Editor', [
            'data' => $item,
            'accounts' => $this->getFinanceAccounts(),
            'settings' => [
                'default_payment_mode' => Setting::value('pos.default_payment_mode', 'cash'),
                'default_print_size' => Setting::value('pos.default_print_size', '58mm')
            ]
        ]);
    }

    public function update(Request $request)
    {
        $item = SalesOrder::find($request->post('id'));

        if ($item->status != SalesOrder::Status_Draft) {
            return JsonResponseHelper::error('Order tidak dapat diubah.', 403);
        }

        $customer = Customer::find($request->post('customer_id'));

        // Nilai awal customer info dari saat diganti customer
        $item->customer_id = $customer ? $customer->id : null;
        $item->customer_code = $customer?->code;
        $item->customer_name = $customer?->name;
        $item->customer_phone = $customer?->phone;
        $item->customer_address = $customer?->address;

        $item->notes = $request->post('notes', '');
        $item->datetime = $request->post('datetime', Carbon::now());

        // FIXME: Saat ini memang belum dibutuhkan karena gak bisa diubah dari client
        // $item->status = $request->post('status', SalesOrder::Status_Draft);
        // $item->payment_status = $request->post('payment_status', SalesOrder::PaymentStatus_Unpaid);
        // $item->delivery_status = $request->post('delivery_status', SalesOrder::DeliveryStatus_ReadyForPickUp);

        $item->save();

        return JsonResponseHelper::success($item, 'Order telah diperbarui');
    }

    public function cancel($id)
    {
        $item = SalesOrder::findOrFail($id);
        if ($item->status == SalesOrder::Status_Draft) {
            $item->status = SalesOrder::Status_Canceled;
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
        $order = SalesOrder::with([
            'cashierSession',
            'details',
            'payments',
            'details.product',
            'payments.account'
        ])->findOrFail($id);

        $cashierSession = $order->cashierSession;
        if ($cashierSession && $cashierSession->is_closed) {
            return JsonResponseHelper::error(
                "Transaksi tidak dapat dihapus! Sesi kasir untuk Order #$order->formatted_id sudah ditutup!",
                403
            );
        }

        try {
            DB::beginTransaction();
            if ($order->status == SalesOrder::Status_Closed) {
                // refund stok
                foreach ($order->details as $detail) {
                    $product = $detail->product;
                    $product->stock += abs($detail->quantity);
                    $product->save();

                    DB::delete(
                        'DELETE FROM stock_movements WHERE ref_type = ? AND ref_id = ?',
                        [StockMovement::RefType_SalesOrderDetail, $detail->id]
                    );
                }

                foreach ($order->payments as $payment) {
                    $amount = abs($payment->amount);

                    // refund customer wallet
                    if ($payment->type == SalesOrderPayment::Type_Wallet) {
                        $customer = $payment->customer;
                        $customer->wallet_balance += $amount;
                        $customer->save();

                        $walletTx = CustomerWalletTransaction::where('ref_type', '=', CustomerWalletTransaction::RefType_SalesOrderPayment)
                            ->where('ref_id', '=', $payment->id)
                            ->get()->first();
                        if ($walletTx) {
                            $walletTx->delete();
                        }
                    }
                    // restore saldo akun
                    else if (
                        $payment->type == SalesOrderPayment::Type_Transfer
                        || $payment->type == SalesOrderPayment::Type_Cash
                    ) {
                        $this->financeTransactionService->reverseTransaction($payment->id, FinanceTransaction::RefType_SalesOrderPayment);
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
        return inertia('sales-order/Detail', [
            'data' => SalesOrder::with([
                'cashier',
                'customer',
                'details',
                'payments',
                'payments.account',
                'cashierSession',
                'cashierSession.cashierTerminal'
            ])
                ->findOrFail($id),
            'accounts' => $this->getFinanceAccounts()
        ]);
    }

    public function print($id, Request $request)
    {
        $item = SalesOrder::with([
            'cashier',
            'cashierSession',
            'cashierSession.cashierTerminal',
            'customer',
            'details',
        ])->findOrFail($id);

        $size = $request->get('size', 'a4');
        if (!in_array($size, ['a4', '58mm'])) {
            $size = 'a4';
        }

        if ($request->get('output') == 'pdf') {
            $pdf = Pdf::loadView('modules.admin.pages.sales-order.print-' . $size, [
                'item' => $item,
                'pdf'  => true,
            ])
                ->setPaper($paper, 'portrait')
                ->setOption('isHtml5ParserEnabled', true)
                ->setOption('isPhpEnabled', true);
            return $pdf->download(env('APP_NAME') . '_' . $item->formatted_id . '.pdf');
        }

        return view('modules.admin.pages.sales-order.print-' . $size, [
            'item' => $item,
        ]);
    }

    public function addItem(Request $request)
    {
        $order = SalesOrder::find($request->post('order_id'));
        if (!$order) {
            return JsonResponseHelper::error('Order tidak ditemukan');
        }

        if ($order->status !== SalesOrder::Status_Draft) {
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
        $price = $product->price;
        if ($product->price_editable && $request->has('price') && $request->post('price') !== null) {
            $price = $request->post('price', 0);
        }

        $detail = null;
        if ($merge) {
            // kalo gabung cari rekaman yang sudah ada
            $detail = SalesOrderDetail::where('parent_id', '=', $order->id)
                ->where('product_id', '=', $product->id)
                ->get()
                ->first();
        }

        if ($detail) {
            // kurangi dulu dengan subtotal sebelum hitungan baru
            $order->total_cost  -= $detail->subtotal_cost;
            $order->total_price -= $detail->subtotal_price;

            // kalau sudah ada cukup tambaih qty saja
            $detail->quantity += $quantity;

            // perbarui subtotal
            $detail->subtotal_cost  = $detail->cost  * $detail->quantity;
            $detail->subtotal_price = $detail->price * $detail->quantity;
        } else {
            $detail = new SalesOrderDetail([
                'parent_id' => $order->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_barcode' => $product->barcode,
                'product_uom' => $product->uom,
                'quantity' => $quantity,
                'cost' => $product->cost,
                'subtotal_cost' => $quantity * $product->cost,
                'price' => $price,
                'subtotal_price' => $quantity * $price,
                'notes' => '',
            ]);
        }

        DB::beginTransaction();
        $detail->save();

        // update total dan subtotal baru
        $order->total_cost  += $detail->subtotal_cost;
        $order->total_price += $detail->subtotal_price;
        $order->save();

        DB::commit();

        return JsonResponseHelper::success([
            'item' => $detail,
            'mergeItem' => $merge,
        ], 'Item telah ditambahkan');
    }

    public function updateItem(Request $request)
    {
        $detail = SalesOrderDetail::find($request->post('id'));
        if (!$detail) {
            return JsonResponseHelper::error('Detail Order tidak ditemukan');
        }

        $order = $detail->parent;

        if ($order->status !== SalesOrder::Status_Draft) {
            return JsonResponseHelper::error('Order sudah tidak dapat diubah.');
        }

        $order->total_cost  -= $detail->subtotal_cost;
        $order->total_price -= $detail->subtotal_price;

        $detail->quantity = $request->post('qty', 0);

        $price = $request->post('price');
        if ($price !== null && $price >= 0) {
            $detail->price = $price;
        }

        // perbarui subtotal
        $detail->subtotal_cost  = $detail->cost  * $detail->quantity;
        $detail->subtotal_price = $detail->price * $detail->quantity;
        $detail->notes = $request->post('notes', '');

        DB::beginTransaction();
        $detail->save();

        // update total dan subtotal baru
        $order->total_cost  += $detail->subtotal_cost;
        $order->total_price += $detail->subtotal_price;
        // FIX ME: ganti perhitungan jika sudah ada pajak dan diskon
        $order->grand_total = $order->total_price;
        $order->save();

        DB::commit();

        return JsonResponseHelper::success($detail, 'Item telah diperbarui.');
    }

    public function removeItem(Request $request)
    {
        $item = SalesOrderDetail::find($request->id);

        if (!$item) {
            return JsonResponseHelper::error('Item tidak ditemukan');
        }

        if ($item->parent->status !== SalesOrder::Status_Draft) {
            return JsonResponseHelper::error('Order selesai tidak dapat diubah!');
        }

        DB::beginTransaction();
        $order = $item->parent;
        $order->total_cost  -= $item->subtotal_cost;
        $order->total_price -= $item->subtotal_price;
        $order->save();
        $item->delete();
        DB::commit();
        return JsonResponseHelper::success($item, 'Item telah dihapus.');
    }

    public function close(Request $request)
    {
        $order = SalesOrder::with(['customer', 'details', 'details.product'])->find($request->post('id'));

        if (!$order) {
            return JsonResponseHelper::error('Item tidak ditemukan');
        }

        if ($order->status !== SalesOrder::Status_Draft) {
            return JsonResponseHelper::error('Order selesai tidak dapat diubah!');
        }

        try {
            DB::beginTransaction();

            // 1. Hitung total biaya dan harga di server
            // Anggap kita punya helper parseToInt untuk semua angka
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

                if ($inputPayment['id'] === 'cash') {
                    $type = SalesOrderPayment::Type_Cash;

                    // ambil akun dimana sesi aktif berjalan untuk user ini
                    $session = CashierSessionService::getActiveSession();

                    if (!$session) {
                        // Todo: mungkin bisa handle auto select / default cash account atau
                        // bisa pilih secara spesifik di payment untuk tangani penjualan
                        // tanpa harus memulai sesi kasir
                        DB::rollBack();
                        throw new Exception("Anda belum memulai sesi kasir.");
                    }

                    $accountId = $session->cashierTerminal->financeAccount->id;
                } else if ($inputPayment['id'] === 'wallet') {
                    $type = SalesOrderPayment::Type_Wallet;
                } else if (intval($inputPayment['id'])) {
                    $type = SalesOrderPayment::Type_Transfer;
                    $accountId = (int)$inputPayment['id'];
                }

                $payment = new SalesOrderPayment([
                    'order_id' => $order->id,
                    'finance_account_id' => $accountId,
                    'customer_id' => $order->customer?->id,
                    'type' => $type,
                    'amount' => $amount,
                ]);
                $payment->save();

                // Catat transaksi keuangan dan perbarui saldo
                if ($type === SalesOrderPayment::Type_Transfer || $type === SalesOrderPayment::Type_Cash) {
                    FinanceTransaction::create([
                        'account_id' => $accountId,
                        'datetime' => now(),
                        'type' => FinanceTransaction::Type_Income,
                        'amount' => $amount,
                        'ref_id' => $payment->id,
                        'ref_type' => FinanceTransaction::RefType_SalesOrderPayment,
                        'notes' => "Pembayaran transaksi #$order->formatted_id",
                    ]);
                    FinanceAccount::where('id', $accountId)->increment('balance', $amount);
                } else if ($type === SalesOrderPayment::Type_Wallet) {
                    CustomerWalletTransaction::create([
                        'customer_id' => $order->customer->id,
                        'datetime' => now(),
                        'type' => CustomerWalletTransaction::Type_SalesOrderPayment,
                        'amount' => -$amount,
                        'ref_type' => CustomerWalletTransaction::RefType_SalesOrderPayment,
                        'ref_id' => $payment->id,
                        'notes' => "Pembayaran transaksi #$order->formatted_id",
                    ]);
                    Customer::where('id', $order->customer->id)->decrement('wallet_balance', $amount);
                }
            }

            // 4. Update status pembayaran dan total yang dibayar
            $order->total_paid = $totalPaidAmount;
            $order->change = max(0, $order->total_paid - $order->grand_total);
            $order->remaining_debt = max(0, $order->grand_total - $order->total_paid);

            if ($order->total_paid >= $order->grand_total) {
                $order->payment_status = SalesOrder::PaymentStatus_FullyPaid;
            } else if ($order->total_paid > 0) {
                $order->payment_status = SalesOrder::PaymentStatus_PartiallyPaid;
            } else {
                $order->payment_status = SalesOrder::PaymentStatus_Unpaid;
            }

            $cashierSession = CashierSessionService::getActiveSession();

            // FIXME: status langsung diambil tanpa harus seting di order
            $order->delivery_status = SalesOrder::DeliveryStatus_PickedUp;
            $order->status = SalesOrder::Status_Closed;
            $order->due_date = $request->post('due_date', null);
            $order->cashier_id = Auth::user()->id;
            $order->cashier_session_id = $cashierSession ? $cashierSession->id : null;
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
                    'ref_type'        => StockMovement::RefType_SalesOrderDetail,
                    'quantity'        => -$quantity,
                    'quantity_before' => $detail->product->stock,
                    'quantity_after'  => $detail->product->stock - $quantity,
                    'notes'           => "Transaksi penjualan #$order->formatted_id",
                ]);

                Product::where('id', $detail->product_id)->decrement('stock', $quantity);
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
        $order = SalesOrder::with(['customer', 'details'])->find($request->post('order_id'));

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

                if ($inputPayment['id'] === 'cash') {
                    $type = SalesOrderPayment::Type_Cash;
                    $session = CashierSessionService::getActiveSession();

                    if (!$session) {
                        throw new Exception("Anda belum memulai sesi kasir.");
                    }

                    $accountId = $session->cashierTerminal->financeAccount->id;
                } else if ($inputPayment['id'] === 'wallet') {
                    $type = SalesOrderPayment::Type_Wallet;
                    if ($order->customer) {
                        // Backend validation for wallet balance
                        if ($order->customer->wallet_balance < $amount) {
                            throw new Exception("Saldo wallet pelanggan tidak mencukupi.");
                        }
                    }
                } else if (intval($inputPayment['id'])) {
                    $type = SalesOrderPayment::Type_Transfer;
                    $accountId = (int)$inputPayment['id'];
                }

                $payment = new SalesOrderPayment([
                    'order_id' => $order->id,
                    'finance_account_id' => $accountId,
                    'customer_id' => $order->customer?->id,
                    'type' => $type,
                    'amount' => $amount,
                ]);
                $payment->save();

                if ($type === SalesOrderPayment::Type_Transfer || $type === SalesOrderPayment::Type_Cash) {
                    FinanceTransaction::create([
                        'account_id' => $accountId,
                        'datetime' => now(),
                        'type' => FinanceTransaction::Type_Income,
                        'amount' => $amount,
                        'ref_id' => $payment->id,
                        'ref_type' => FinanceTransaction::RefType_SalesOrderPayment,
                        'notes' => "Pembayaran transaksi #$order->formatted_id",
                    ]);
                    FinanceAccount::where('id', $accountId)->increment('balance', $amount);
                } else if ($type === SalesOrderPayment::Type_Wallet) {
                    CustomerWalletTransaction::create([
                        'customer_id' => $order->customer->id,
                        'datetime' => now(),
                        'type' => CustomerWalletTransaction::Type_SalesOrderPayment,
                        'amount' => -$amount,
                        'ref_type' => CustomerWalletTransaction::RefType_SalesOrderPayment,
                        'ref_id' => $payment->id,
                        'notes' => "Pembayaran transaksi #$order->formatted_id",
                    ]);
                    Customer::where('id', $order->customer->id)->decrement('wallet_balance', $amount);
                }
            }

            $order->total_paid += $totalPaidAmount;
            $order->remaining_debt = max(0, $order->grand_total - $order->total_paid);

            if ($order->total_paid >= $order->grand_total) {
                $order->payment_status = SalesOrder::PaymentStatus_FullyPaid;
            } else if ($order->total_paid > 0) {
                $order->payment_status = SalesOrder::PaymentStatus_PartiallyPaid;
            } else {
                $order->payment_status = SalesOrder::PaymentStatus_Unpaid;
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
    public function deletePayment(Request $request)
    {
        $id = $request->id;

        DB::beginTransaction();

        try {
            // 1. Cari pembayaran berdasarkan ID
            $payment = SalesOrderPayment::with(['order', 'order.customer'])->find($id);

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

            if ($order->status !== SalesOrder::Status_Closed) {
                DB::rollBack();
                return JsonResponseHelper::error('Tidak dapat menghapus pembayaran dari order yang belum selesai.');
            }

            // 3. Batalkan transaksi keuangan terkait berdasarkan tipe pembayaran
            if ($payment->type === SalesOrderPayment::Type_Transfer || $payment->type === SalesOrderPayment::Type_Cash) {
                // Batalkan transaksi keuangan
                FinanceTransaction::where('ref_id', $payment->id)
                    ->where('ref_type', FinanceTransaction::RefType_SalesOrderPayment)
                    ->delete();

                // Kurangi saldo akun keuangan
                if ($payment->finance_account_id) {
                    FinanceAccount::where('id', $payment->finance_account_id)
                        ->decrement('balance', $payment->amount);
                }
            } else if ($payment->type === SalesOrderPayment::Type_Wallet) {
                // Batalkan transaksi dompet pelanggan
                CustomerWalletTransaction::where('ref_id', $payment->id)
                    ->where('ref_type', CustomerWalletTransaction::RefType_SalesOrderPayment)
                    ->delete();

                // Tambahkan kembali saldo ke dompet pelanggan
                if ($order->customer) {
                    Customer::where('id', $order->customer->id)
                        ->increment('wallet_balance', $payment->amount);
                }
            }

            // 4. Hapus entri pembayaran dari database
            $payment->delete();

            // 5. Perbarui status order
            $order->total_paid -= $payment->amount;
            $order->remaining_debt = max(0, $order->grand_total - $order->total_paid);

            if ($order->total_paid >= $order->grand_total) {
                $order->payment_status = SalesOrder::PaymentStatus_FullyPaid;
            } else if ($order->total_paid > 0) {
                $order->payment_status = SalesOrder::PaymentStatus_PartiallyPaid;
            } else {
                $order->payment_status = SalesOrder::PaymentStatus_Unpaid;
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
