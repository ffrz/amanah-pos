<?php

namespace Modules\Admin\Http\Controllers;

use App\Helpers\JsonResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\SalesOrder;
use App\Models\Customer;
use App\Models\FinanceAccount;
use App\Models\Product;
use App\Models\SalesOrderDetail;
use App\Models\SalesOrderPayment;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
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
        allowed_roles([User::Role_Admin]);

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
                ->where('type', '<>', 'cash')
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
        allowed_roles([User::Role_Admin]);

        $item = SalesOrder::findOrFail($id);

        // TODO: handle jika statusnya closed harus di restock

        $item->delete();

        return response()->json([
            'message' => "Transaksi #$item->formatted_id telah dihapus.",
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
        $order = SalesOrder::with('details')->find($request->post('id'));

        if (!$order) {
            return JsonResponseHelper::error('Item tidak ditemukan');
        }

        if ($order->status !== SalesOrder::Status_Draft) {
            return JsonResponseHelper::error('Order selesai tidak dapat diubah!');
        }

        $details = $order->details;
        $total_cost = 0;
        $total_price = 0;
        foreach ($details as $detail) {
            $total_cost += $detail->cost * $detail->quantity;
            $total_price += $detail->price * $detail->quantity;
        }

        $order->total_cost = $total_cost;
        $order->total_price = $total_price;

        // karena belum ada pajak dan diskon, grand total selalu dali total harga jual
        $order->grand_total = $order->total_price;

        // TODO: ini validasi lanjutan untuk memastikan total yang ditampilkan di client sama dengan jumlah total di server
        if (floatval($order->grand_total) !== floatval($request->post('total', 0))) {
            return JsonResponseHelper::error('Gagal menyimpan transaksi, coba refresh halaman!');
        }

        $order->due_date = $request->post('due_date', null);
        if (!$request->post('is_debt', false)) {
            $order->total_paid = $request->post('total', 0);
        }
        $order->change = $request->post('change', 0);
        $order->remaining_debt = $request->post('remaining_debt', 0);

        // tutup status
        $order->status = SalesOrder::Status_Closed;
        if ($order->total_paid >= $order->grand_total) {
            $order->payment_status = SalesOrder::PaymentStatus_FullyPaid;
        } else if ($order->total_paid > 0) {
            $order->payment_status = SalesOrder::PaymentStatus_PartiallyPaid;
        } else {
            $order->payment_status = SalesOrder::PaymentStatus_Unpaid;
        }

        DB::beginTransaction();
        $order->save();
        $payments = $request->post('payments', []);
        foreach ($payments as $inputPayment) {
            if (!isset($inputPayment['id'])) {
                return JsonResponseHelper::error('Invalid payment input format!');
            }

            $account_id = null;
            $type = 'cash'; // TODO: Jadikan konstanta di model
            if ($inputPayment['id'] == 'cash') {
                $type = 'cash'; // TODO: Jadikan konstanta di model
                // TODO: disini harus catat transaksi kasir dan tambahkan uang kas di kasir
            } else if ($inputPayment['id'] == 'wallet') {
                $type = 'wallet'; // TODO: Jadikan konstanta di model
                // TODO: disini harus cata transaksi wallet dan kurangi saldo wallet milik pealnggan
            } else if ($id = intval($inputPayment['id'])) {
                $type = 'transer'; // TODO: Jadikan konstanta di model
                $account_id = $id;
                // TODO: disini harus catat transaksi di kas dan tambahkan saldo akun kas tersebut
            }

            $payment = new SalesOrderPayment([
                'order_id' => $order->id,
                'finance_account_id' => $account_id ? $account_id : null,
                'customer_id' => $order->customer_id ? $order->customer_id : null,
                'type' => $type,
            ]);
            $payment->type = $type;
            $payment->amount = floatval($inputPayment['amount']);
            $payment->save();
        }

        DB::commit();

        return JsonResponseHelper::success($order, "Order telah selesai.");
    }
}
