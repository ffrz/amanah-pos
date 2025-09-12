<?php

namespace Modules\Admin\Http\Controllers;

use App\Helpers\JsonResponseHelper;
use App\Helpers\NumberHelper;
use App\Http\Controllers\Controller;
use App\Models\SalesOrder;
use App\Models\Customer;
use App\Models\CustomerWalletTransaction;
use App\Models\FinanceAccount;
use App\Models\FinanceTransaction;
use App\Models\Product;
use App\Models\SalesOrderDetail;
use App\Models\SalesOrderPayment;
use App\Models\StockMovement;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SalesOrderController extends Controller
{
    public function index()
    {
        return inertia('sales-order/Index');
    }

    public function data(Request $request)
    {
        $orderBy = $request->get('order_by', 'datetime');
        $orderType = $request->get('order_type', 'desc');
        $filter = $request->get('filter', []);

        $q = SalesOrder::with('customer');

        if (!empty($filter['search'])) {
            $q->where(function ($q) use ($filter) {
                $q->where('description', 'like', '%' . $filter['search'] . '%');
                $q->orWhere('notes', 'like', '%' . $filter['search'] . '%');
            });
        }

        if (!empty($filter['status']) && $filter['status'] != 'all') {
            $q->where('status', '=', $filter['status']);
        }

        if (!empty($filter['payment_status']) && $filter['payment_status'] != 'all') {
            $q->where('payment_status', '=', $filter['payment_status']);
        }

        if (!empty($filter['delivery_status']) && $filter['delivery_status'] != 'all') {
            $q->where('delivery_status', '=', $filter['delivery_status']);
        }

        // Tambahan filter tahun
        if (!empty($filter['year']) && $filter['year'] !== 'all') {
            $q->whereYear('datetime', $filter['year']);

            if (!empty($filter['month']) && $filter['month'] !== 'all') {
                $q->whereMonth('datetime', $filter['month']);
            }
        }

        $q->select(['id', 'total_price', 'datetime', 'status', 'payment_status', 'delivery_status'])
            ->orderBy($orderBy, $orderType);

        $items = $q->paginate($request->get('per_page', 10))->withQueryString();

        return response()->json($items);
    }

    public function editor($id = 0)
    {
        // allowed_roles([User::Role_Admin]);

        if (!$id) {
            $item = new SalesOrder([
                'datetime' => Carbon::now(),
                'status' => SalesOrder::Status_Draft,
                'payment_status' => SalesOrder::PaymentStatus_Unpaid,
                'delivery_status' => SalesOrder::DeliveryStatus_NotSent,
            ]);
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
            'accounts' => FinanceAccount::where('active', '=', true)
                ->where(function ($query) {
                    $query->where('type', '=', FinanceAccount::Type_Cash)
                        ->orWhere('type', '=', FinanceAccount::Type_Bank)
                        ->orWhere('type', '=', FinanceAccount::Type_PettyCash);
                })
                ->where('show_in_pos_payment', '=', true)
                ->orderBy('name')
                ->get()
        ]);
    }

    public function update(Request $request)
    {
        $item = SalesOrder::find($request->post('id'));

        if ($item->status != SalesOrder::Status_Draft) {
            return JsonResponseHelper::error('Order tidak dapat diubah.', 403);
        }

        $item->customer_id = $request->post('customer_id');
        $item->notes = $request->post('notes', '');
        $item->datetime = $request->post('datetime', Carbon::now());
        $item->status = $request->post('status', SalesOrder::Status_Draft);
        $item->payment_status = $request->post('payment_status', SalesOrder::PaymentStatus_Unpaid);
        $item->delivery_status = $request->post('delivery_status', SalesOrder::DeliveryStatus_NotSent);

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
        // allowed_roles([User::Role_Admin]);

        $order = SalesOrder::with([
            'details',
            'payments',
            'details.product',
            'payments.account'
        ])
            ->findOrFail($id);

        DB::beginTransaction();
        if ($order->status == SalesOrder::Status_Closed) {
            // refund stok
            foreach ($order->details as $detail) {
                $product = $detail->product;
                $product->stock += abs($detail->quantity);
                $product->save();

                $movement = StockMovement::where('ref_type', '=', StockMovement::RefType_SalesOrderDetail)
                    ->where('ref_id', '=', $detail->id)
                    ->get()->first();
                if ($movement) {
                    $movement->delete();
                }
            }

            foreach ($order->payments as $payment) {
                $amount = abs($payment->amount);

                // refund customer wallet
                if ($payment->type == SalesOrderPayment::Type_Wallet) {
                    $customer = $payment->customer;
                    $customer->balance += $amount;
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
                    $account = $payment->account;
                    $account->balance -= $amount;
                    $account->save();

                    $financeTx = FinanceTransaction::where('ref_type', '=', FinanceTransaction::RefType_SalesOrderPayment)
                        ->where('ref_id', '=', $payment->id)
                        ->get()->first();
                    if ($financeTx) {
                        $financeTx->delete();
                    }
                }

                $payment->delete();
            }
        }

        $order->delete();
        DB::commit();

        return response()->json([
            'message' => "Transaksi #$order->formatted_id telah dihapus.",
        ]);
    }

    public function detail($id)
    {
        return inertia('sales-order/Detail', [
            'data' => SalesOrder::with(['customer', 'details'])
                ->findOrFail($id),
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
                    // Ambil account ID kasir dari user yang login
                    $accountId = Auth::user()->cashier_account_id;
                    if (!$accountId) {
                        DB::rollBack();
                        throw new Exception("Akun kas belum diset!");
                    }
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
                    Customer::where('id', $order->customer->id)->decrement('balance', $amount);
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

            $order->status = SalesOrder::Status_Closed;
            $order->due_date = $request->post('due_date', null);
            $order->save();

            // 5. Perbarui stok produk secara massal
            foreach ($order->details as $detail) {
                $quantity = $detail->quantity;
                StockMovement::create([
                    'product_id' => $detail->product_id,
                    'ref_id'     => $detail->id,
                    'ref_type'   => StockMovement::RefType_SalesOrderDetail,
                    'quantity'   => -$quantity,
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
}
