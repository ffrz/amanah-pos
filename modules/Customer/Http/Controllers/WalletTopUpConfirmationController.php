<?php

namespace Modules\Customer\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CustomerWalletTopUpConfirmation;
use App\Models\CustomerWalletTransaction;
use App\Models\CustomerWalletTransactionConfirmation;
use App\Models\FinanceAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Admin\Http\Controllers\CustomerWalletTransactionController;

class WalletTopUpConfirmationController extends Controller
{

    public function index()
    {
        return inertia('wallet-topup-confirmation/Index', []);
    }

    public function add()
    {
        return inertia('wallet-topup-confirmation/Editor', [
            'accounts' => FinanceAccount::where('has_wallet_access', true)->orderBy('name')->get()
        ]);
    }

    public function data(Request $request)
    {
        $orderBy = $request->get('order_by', 'datetime');
        $orderType = $request->get('order_type', 'desc');
        $filter = $request->get('filter', []);

        $currentUserId = Auth::guard('customer')->user()->id;

        $q = CustomerWalletTransactionConfirmation::with(['financeAccount:id,name,bank,number,holder'])
            ->where('customer_id', $currentUserId);

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

    public function save(Request $request)
    {
        $validated = $request->validate([
            'finance_account_id' => 'required|exists:finance_accounts,id',
            'datetime' => 'required|date',
            'amount'   => 'required|numeric|min:0.01',
            'notes'    => 'nullable|string|max:255',
        ]);

        $validated['customer_id'] = Auth::guard('customer')->user()->id;
        $validated['status'] = CustomerWalletTransactionConfirmation::Status_Pending;
        $topUpConfirmation = new CustomerWalletTransactionConfirmation($validated);
        $topUpConfirmation->save();

        return redirect(route('customer.wallet-topup-confirmation.index'))
            ->with('success', 'Konfirmasi topup telah disimpan.');
    }
}
