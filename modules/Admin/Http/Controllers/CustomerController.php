<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class CustomerController extends Controller
{
    public function index()
    {
        return inertia('customer/Index');
    }

    public function detail($id = 0)
    {
        return inertia('customer/Detail', [
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
        return inertia('customer/Editor', [
            'data' => $item,
        ]);
    }

    public function editor($id = 0)
    {
        allowed_roles([User::Role_Admin, User::Role_Cashier]);
        $item = $id ? Customer::findOrFail($id) : new Customer(['active' => true]);
        return inertia('customer/Editor', [
            'data' => $item,
        ]);
    }

    public function save(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:40|unique:customers,username' . ($request->id ? ',' . $request->id : ''),
            'name' => 'required|max:255',
            'parent_name' => 'nullable|max:255',
            'phone' => 'required|max:100',
            'address' => 'required|max:1000',
            'active'   => 'required|boolean',
            'password' => (!$request->id ? 'required|' : '') . 'min:5|max:40',
        ]);

        $item = !$request->id ? new Customer() : Customer::findOrFail($request->id);
        if (!$request->id) {
            $validated['balance'] = 0;
        }

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $item->fill($validated);
        $item->save();

        return redirect(route('admin.customer.index'))->with('success', "Santri $item->name telah disimpan.");
    }

    public function delete($id)
    {
        throw new Exception('Fitur sementara waktu dinonaktifkan, silahkan hubungi developer!');

        // boleh dihapus???
        allowed_roles([User::Role_Admin]);

        $item = Customer::findOrFail($id);
        $item->delete();

        return response()->json([
            'message' => "Santri $item->name telah dihapus."
        ]);
    }

    public function getBalance(Request $request)
    {
        $customer = Customer::findOrFail($request->id);
        return response()->json(['balance' => $customer->balance]);
    }
}
