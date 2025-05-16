<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerWalletTransaction;
use App\Models\User;
use Illuminate\Http\Request;

class CustomerWalletTransactionController extends Controller
{

    public function index()
    {
        return inertia('admin/customer-wallet-transaction/Index', [

        ]);
    }

    public function data(Request $request)
    {
        $orderBy = $request->get('order_by', 'datetime');
        $orderType = $request->get('order_type', 'desc');
        $filter = $request->get('filter', []);

        $q = CustomerWalletTransaction::with('customer');

        if (!empty($filter['search'])) {
            $q->where(function ($q) use ($filter) {
                // $q->where('description', 'like', '%' . $filter['search'] . '%');
                $q->orWhere('notes', 'like', '%' . $filter['search'] . '%');
            });
        }

        // if (!empty($filter['category_id'])) {
        //     if ($filter['category_id'] === 'null') {
        //         $q->whereNull('category_id');
        //     } else if ($filter['category_id'] !== 'all') {
        //         $q->where('category_id', '=', $filter['category_id']);
        //     }
        // }

        // Tambahan filter tahun
        // if (!empty($filter['year']) && $filter['year'] !== 'null') {
        //     $q->whereYear('date', $filter['year']);

        //     if (!empty($filter['month']) && $filter['month'] !== 'null') {
        //         $q->whereMonth('date', $filter['month']);
        //     }
        // }

        $q->orderBy($orderBy, $orderType);

        $items = $q->paginate($request->get('per_page', 10))->withQueryString();

        return response()->json($items);
    }

    // public function duplicate($id)
    // {
    //     allowed_roles([User::Role_Admin]);
    //     $item = CustomerWalletTransaction::findOrFail($id);
    //     $item->id = null;
    //     return inertia('admin/customer-wallet-transaction/Editor', [
    //         'data' => $item,
    //     ]);
    // }

    public function editor($id = 0)
    {
        allowed_roles([User::Role_Admin, User::Role_Cashier]);
        $item = $id ? CustomerWalletTransaction::findOrFail($id) : new CustomerWalletTransaction(['datetime' => date('Y-m-d H:i:s')]);
        dd(Customer::where('active', '=', true)->orderBy('nis', 'asc')->get());
        return inertia('admin/customer-wallet-transaction/Editor', [
            'data' => $item,
            'customers' => Customer::where('active', '=', true)->orderBy('nis', 'asc')->get()
        ]);
    }

    public function save(Request $request)
    {
        $rules = [
            'date' => 'required|date',
            'category_id' => 'nullable',
            'description' => 'required|max:255',
            'amount' => 'required|numeric|gt:0',
            'notes' => 'nullable|max:1000',
        ];

        $item = null;
        $message = '';
        $fields = ['date', 'description', 'amount', 'notes', 'category_id'];

        $request->validate($rules);

        if (!$request->id) {
            $item = new CustomerWalletTransaction();
            $message = 'customer-wallet-transaction-created';
        } else {
            $item = CustomerWalletTransaction::findOrFail($request->post('id', 0));
            $message = 'customer-wallet-transaction-updated';
        }

        $data = $request->only($fields);
        $data['notes'] = $data['notes'] ?? '';

        // todo, update balance

        $item->fill($data);
        $item->save();

        return redirect(route('admin.customer-wallet-transaction.index'))
            ->with('success', __("messages.$message", ['description' => $item->description]));
    }

    public function delete($id)
    {
        allowed_roles([User::Role_Admin]);

        $item = CustomerWalletTransaction::findOrFail($id);
        // todo, restore balance
        $item->delete();

        return response()->json([
            'message' => __('messages.customer-wallet-transaction-deleted', ['description' => $item->description])
        ]);
    }
}
