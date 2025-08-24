<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SalesOrder;
use App\Models\Customer;
use App\Models\User;
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
        if (!empty($filter['year']) && $filter['year'] !== 'null') {
            $q->whereYear('datetime', $filter['year']);

            if (!empty($filter['month']) && $filter['month'] !== 'null') {
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
        $item = $id ? SalesOrder::findOrFail($id) : new SalesOrder(['date' => date('Y-m-d')]);
        return inertia('sales-order/Editor', [
            'data' => $item,
            'customers' => Customer::where('active', '=', true)->orderBy('username', 'asc')->get(),
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

    public function delete($id)
    {
        allowed_roles([User::Role_Admin]);

        $item = SalesOrder::findOrFail($id);
        $item->delete();

        return response()->json([
            'message' => __('messages.sales-order-deleted', ['description' => $item->description])
        ]);
    }
}
