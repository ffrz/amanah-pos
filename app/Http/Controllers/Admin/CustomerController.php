<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    public function index()
    {
        return inertia('admin/customer/Index');
    }

    public function detail($id = 0)
    {
        return inertia('admin/customer/Detail', [
            'data' => Customer::findOrFail($id),
        ]);
    }

    public function data(Request $request)
    {
        $orderBy = $request->get('order_by', 'name');
        $orderType = $request->get('order_type', 'asc');
        $filter = $request->get('filter', []);

        $q = Customer::query();

        if (!empty($filter['search'])) {
            $q->where(function ($q) use ($filter) {
                $q->where('name', 'like', '%' . $filter['search'] . '%');
                $q->orWhere('phone', 'like', '%' . $filter['search'] . '%');
                $q->orWhere('address', 'like', '%' . $filter['search'] . '%');
            });
        }

        if (!empty($filter['status']) && ($filter['status'] == 'active' || $filter['status'] == 'inactive')) {
            $q->where('active', '=', $filter['status'] == 'active' ? true : false);
        }

        $q->orderBy($orderBy, $orderType);

        $items = $q->paginate($request->get('per_page', 10))->withQueryString();

        return response()->json($items);
    }

    public function duplicate($id)
    {
        allowed_roles([User::Role_Admin, User::Role_Cashier]);
        $item = Customer::findOrFail($id);
        $item->id = null;
        $item->created_at = null;
        return inertia('admin/customer/Editor', [
            'data' => $item,
        ]);
    }

    public function editor($id = 0)
    {
        allowed_roles([User::Role_Admin, User::Role_Cashier]);
        $item = $id ? Customer::findOrFail($id) : new Customer(['active' => true]);
        return inertia('admin/customer/Editor', [
            'data' => $item,
        ]);
    }

    public function save(Request $request)
    {
        $rules = [
            'name' => 'required|max:255',
            'phone' => 'required|max:100',
            'address' => 'required|max:1000',
        ];

        $item = null;
        $message = '';
        $fields = ['name', 'phone', 'address', 'active'];

        $request->validate($rules);

        if (!$request->id) {
            $item = new Customer();
            $message = 'customer-created';
        } else {
            $item = Customer::findOrFail($request->post('id', 0));
            $message = 'customer-updated';
        }

        $item->fill($request->only($fields));
        $item->save();

        return redirect(route('admin.customer.index'))->with('success', __("messages.$message", ['name' => $item->name]));
    }

    public function delete($id)
    {
        allowed_roles([User::Role_Admin]);

        $item = Customer::findOrFail($id);
        $item->delete();

        return response()->json([
            'message' => __('messages.customer-deleted', ['name' => $item->name])
        ]);
    }
}
