<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerWalletTransaction;
use App\Models\FinanceAccount;
use App\Models\FinanceTransaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerWalletTransactionController extends Controller
{

    public function index()
    {
        return inertia('customer-wallet-transaction/Index', []);
    }

    public function data(Request $request)
    {
        $orderBy = $request->get('order_by', 'datetime');
        $orderType = $request->get('order_type', 'desc');
        $filter = $request->get('filter', []);

        $q = CustomerWalletTransaction::with(['customer', 'financeAccount']);

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

        if (!empty($filter['customer_id']) && $filter['customer_id'] != 'all') {
            $q->where('customer_id', '=', $filter['customer_id']);
        }

        $q->orderBy($orderBy, $orderType);

        $items = $q->paginate($request->get('per_page', 10))->withQueryString();

        return response()->json($items);
    }

    public function editor($id = 0)
    {
        allowed_roles([User::Role_Admin, User::Role_Cashier]);
        $item = $id ? CustomerWalletTransaction::findOrFail($id) : new CustomerWalletTransaction(['datetime' => date('Y-m-d H:i:s')]);
        return inertia('customer-wallet-transaction/Editor', [
            'data' => $item,
            'customers' => Customer::where('active', '=', true)->orderBy('username', 'asc')->get(),
            'finance_accounts' => FinanceAccount::where('active', '=', true)->orderBy('name', 'asc')->get(),
        ]);
    }

    public function save(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'finance_account_id' => 'required|exists:finance_accounts,id',
            'datetime' => 'required|date',
            'type' => 'required|in:' . implode(',', array_keys(CustomerWalletTransaction::Types)),
            'amount' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:255',
        ]);

        if (
            in_array($validated['type'], [
                CustomerWalletTransaction::Type_Purchase,
                CustomerWalletTransaction::Type_Withdrawal,
            ])
        ) {
            $validated['amount'] *= -1;
        }

        $amount = $validated['amount'];

        $validated['notes'] ?? '';

        DB::beginTransaction();
        $item = new CustomerWalletTransaction();
        $item->fill($validated);
        $item->save();

        $customer = $item->customer;
        $customer->balance += $amount;
        $customer->save();

        // hanya digunakan ketika mempengaruhi kas koperasi
        if (in_array($validated['type'], [
            CustomerWalletTransaction::Type_Deposit,
            CustomerWalletTransaction::Type_Withdrawal,
        ])) {
            $account = FinanceAccount::findOrFail($validated['finance_account_id']);
            // jika deposit, maka kas koperasi juga ikut bertambah\
            // jika withdraw, kas koperasi juga akan berkurang
            $account->balance += $amount;
            $account->save();

            // Tambah entri transaksi keuangan
            FinanceTransaction::create([
                'datetime' => $validated['datetime'],
                'account_id' => $validated['finance_account_id'],
                'amount' => $amount,
                'type' => $amount >= 0 ? FinanceTransaction::Type_Income : FinanceTransaction::Type_Expense,
                'notes' => 'Transaksi wallet customer #' . $customer->id,
                'ref_type' => FinanceTransaction::RefType_CustomerWalletTransaction,
                'ref_id' => $item->id,
            ]);
        }

        DB::commit();

        return redirect(route('admin.customer-wallet-transaction.index'))
            ->with('success', "Transaksi $item->id telah disimpan.");
    }

    public function adjustment(Request $request)
    {
        if ($request->getMethod() === 'GET') {
            return inertia('customer-wallet-transaction/Adjustment', [
                'data' => [],
                'customers' => Customer::where('active', '=', true)->orderBy('username', 'asc')->get(),
            ]);
        }

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'new_balance' => 'required|numeric',
            'notes' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();

        $customer = Customer::findOrFail($request->customer_id);

        $old_balance = $customer->balance;
        $customer->balance = $validated['new_balance'];
        $customer->save();

        $item  = new CustomerWalletTransaction([
            'customer_id' => $customer->id,
            'datetime' => Carbon::now(),
            'type' => CustomerWalletTransaction::Type_Adjustment,
            'amount' => $validated['new_balance'] - $old_balance,
            'notes' => $validated['notes'],
        ]);
        $item->save();

        DB::commit();

        return redirect(route('admin.customer-wallet-transaction.index'))
            ->with('success', "Penyesuaian saldo $item->id telah disimpan.");
    }

    public function delete($id)
    {
        allowed_roles([User::Role_Admin]);

        DB::beginTransaction();
        $item = CustomerWalletTransaction::findOrFail($id);
        $item->customer->balance -= $item->amount;
        $item->customer->save();
        $item->delete();

        // Jika transaksi menyentuh kas, kembalikan saldo kas
        if (in_array($item->type, [
            CustomerWalletTransaction::Type_Deposit,
            CustomerWalletTransaction::Type_Withdrawal
        ]) && $item->finance_account_id) {
            $kas = $item->financeAccount;
            $kas->balance -= $item->amount;
            $kas->save();
            FinanceTransaction::where([
                'ref_type' => FinanceTransaction::RefType_CustomerWalletTransaction,
                'ref_id' => $item->id,
            ])->delete();
        }

        DB::commit();

        return response()->json([
            'message' => "Transaksi #$item->id telah dihapus."
        ]);
    }
}
