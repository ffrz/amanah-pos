<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerWalletTransaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerWalletTransactionController extends Controller
{

    public function index()
    {
        return inertia('admin/customer-wallet-transaction/Index', []);
    }

    public function data(Request $request)
    {
        $orderBy = $request->get('order_by', 'datetime');
        $orderType = $request->get('order_type', 'desc');
        $filter = $request->get('filter', []);

        $q = CustomerWalletTransaction::with('customer');

        if (!empty($filter['search'])) {
            $q->where(function ($q) use ($filter) {
                $q->orWhere('notes', 'like', '%' . $filter['search'] . '%');
            });
        }

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

    public function editor($id = 0)
    {
        allowed_roles([User::Role_Admin, User::Role_Cashier]);
        $item = $id ? CustomerWalletTransaction::findOrFail($id) : new CustomerWalletTransaction(['datetime' => date('Y-m-d H:i:s')]);
        return inertia('admin/customer-wallet-transaction/Editor', [
            'data' => $item,
            'customers' => Customer::where('active', '=', true)->orderBy('nis', 'asc')->get()
        ]);
    }

    public function save(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'datetime'    => 'required|date',
            'type'        => 'required|in:' . implode(',', array_keys(CustomerWalletTransaction::Types)),
            'amount'      => 'required|numeric|min:0.01',
            'notes'       => 'nullable|string|max:255',
        ]);

        if ($validated['type'] == CustomerWalletTransaction::Type_Purchase
            || $validated['type'] == CustomerWalletTransaction::Type_Withdrawal) {
                $validated['amount'] *= -1;
        }

        $validated['notes'] ?? '';

        DB::beginTransaction();
        $item = new CustomerWalletTransaction();
        $item->fill($validated);
        $item->save();

        $item->customer->balance += $item->amount;
        $item->customer->save();

        DB::commit();

        return redirect(route('admin.customer-wallet-transaction.index'))
            ->with('success', "Transaksi $item->id telah disimpan.");
    }

    public function delete($id)
    {
        allowed_roles([User::Role_Admin]);

        DB::beginTransaction();
        $item = CustomerWalletTransaction::findOrFail($id);
        $item->customer->balance -= $item->amount;
        $item->customer->save();
        $item->delete();
        DB::commit();

        return response()->json([
            'message' => "Transaksi #$item->id telah dihapus."
        ]);
    }
}
