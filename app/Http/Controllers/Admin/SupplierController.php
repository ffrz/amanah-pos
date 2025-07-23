<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        return inertia('admin/supplier/Index');
    }

    public function detail($id = 0)
    {
        return inertia('admin/supplier/Detail', [
            'data' => Supplier::findOrFail($id),
        ]);
    }

    public function data(Request $request)
    {
        $orderBy = $request->get('order_by', 'name');
        $orderType = $request->get('order_type', 'asc');
        $filter = $request->get('filter', []);

        $q = Supplier::query();

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
        $item = Supplier::findOrFail($id);
        $item->id = null;
        $item->created_at = null;
        return inertia('admin/supplier/Editor', [
            'data' => $item,
        ]);
    }

    public function editor($id = 0)
    {
        allowed_roles([User::Role_Admin, User::Role_Cashier]);
        $item = $id ? Supplier::findOrFail($id) : new Supplier(['active' => true]);
        return inertia('admin/supplier/Editor', [
            'data' => $item,
        ]);
    }

    public function save(Request $request)
    {
        $item = null;
        $message = '';
        $validated = $request->validate([
            'name' => 'required|max:255',
            'phone' => 'required|max:100',
            'bank_account_number' => 'nullable|max:40',
            'active' => 'required|boolean',
            'address' => 'required|max:200',
            'return_address' => 'nullable|max:200',
        ]);

        if (!$request->id) {
            $item = new Supplier();
            $message = 'supplier-created';
        } else {
            $item = Supplier::findOrFail($request->post('id', 0));
            $message = 'supplier-updated';
        }

        $item->fill($validated);
        $item->save();

        return redirect(route('admin.supplier.index'))->with('success', __("messages.$message", ['name' => $item->name]));
    }

    public function delete($id)
    {
        allowed_roles([User::Role_Admin]);

        $item = Supplier::findOrFail($id);
        $item->delete();

        return response()->json([
            'message' => __('messages.supplier-deleted', ['name' => $item->name])
        ]);
    }
}
