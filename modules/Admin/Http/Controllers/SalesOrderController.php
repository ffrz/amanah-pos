<?php

namespace Modules\Admin\Http\Controllers;

use App\Helpers\JsonResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\SalesOrder;
use App\Models\Customer;
use App\Models\Product;
use App\Models\SalesOrderDetail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesOrderController extends Controller
{
    protected function _customers()
    {
        return Customer::where('active', '=', true)
            ->orderBy('username', 'asc')
            ->select(['id', 'username', 'name', 'balance'])
            ->get();
    }

    protected function _products()
    {
        return Product::where('active', '=', true)
            ->orderBy('name', 'asc')
            ->select(['id', 'barcode', 'name', 'description', 'price', 'price_2', 'price_3', 'uom'])
            ->get();
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

    public function duplicate($id)
    {
        allowed_roles([User::Role_Admin]);
        $item = SalesOrder::findOrFail($id);
        $item->id = null;
        return inertia('sales-order/Editor', [
            'data' => $item,
            'categories' => $this->_customers(),
        ]);
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

        // reopen order jika sudah bukan draft
        $item = SalesOrder::with(['details'])->findOrFail($id);
        if ($item->status === SalesOrder::Status_Closed) {
            $item->status = SalesOrder::Status_Draft;
            // TODO: kembalikan stok jika sudah ditutup, atau tolak kalau tidak boleh reopen
            $item->save();
        } else if ($item->status === SalesOrder::Status_Canceled) {
            $item->status = SalesOrder::Status_Draft;
            $item->save();
        }

        return inertia('sales-order/Editor', [
            'data' => $item,
        ]);
    }

    public function save(Request $request)
    {
        $item = null;
        $message = '';

        $validated = $request->validate([
            'date' => 'required|date',
            'customer_id' => 'nullable',
            'description' => 'required|max:255',
            'amount' => 'required|numeric|gt:0',
            'notes' => 'nullable|max:1000',
        ]);

        if (!$request->id) {
            $item = new SalesOrder();
            $message = 'sales-order-created';
        } else {
            $item = SalesOrder::findOrFail($request->post('id', 0));
            $message = 'sales-order-updated';
        }

        $validated['notes'] = $validated['notes'] ?? '';

        $item->fill($validated);
        $item->save();

        return redirect(route('admin.sales-order.index'))
            ->with('success', __("messages.$message", ['description' => $item->description]));
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
            'data' => SalesOrder::findOrFail($id),
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
}
