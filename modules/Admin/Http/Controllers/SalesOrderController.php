<?php

namespace Modules\Admin\Http\Controllers;

use App\Helpers\JsonResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\SalesOrder;
use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SalesOrderController extends Controller
{
    protected function _customers()
    {
        return Customer::all();
    }

    public function index()
    {
        return inertia('sales-order/Index', [
            'categories' => $this->_customers(),
        ]);
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

        if (!empty($filter['customer_id'])) {
            if ($filter['customer_id'] === 'null') {
                $q->whereNull('customer_id');
            } else if ($filter['customer_id'] !== 'all') {
                $q->where('customer_id', '=', $filter['customer_id']);
            }
        }

        // Tambahan filter tahun
        if (!empty($filter['year']) && $filter['year'] !== 'all') {
            $q->whereYear('datetime', $filter['year']);

            if (!empty($filter['month']) && $filter['month'] !== 'all') {
                $q->whereMonth('datetime', $filter['month']);
            }
        }

        $q->orderBy($orderBy, $orderType);

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
        } else {
            // reopen order jika sudah bukan draft
            $item = SalesOrder::findOrFail($id);
            if ($item->status === SalesOrder::Status_Closed) {
                $item->status = SalesOrder::Status_Draft;
                // TODO: kembalikan stok jika sudah ditutup, atau tolak kalau tidak boleh reopen
            } else if ($item->status === SalesOrder::Status_Canceled) {
                $item->status = SalesOrder::Status_Draft;
            }
        }
        $item->save();

        return inertia('sales-order/Editor', [
            'data' => $item,
            'customers' => Customer::where('active', '=', true)->orderBy('username', 'asc')->get(),
            'products' => Product::where('active', '=', true)->orderBy('name', 'asc')->get(),
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
            return JsonResponseHelper::success(['id' => $item->id]);
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
}
