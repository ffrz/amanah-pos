<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\CustomerWalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WalletTopUpConfirmationController extends Controller
{

    public function index()
    {
        return inertia('customer/wallet-topup-confirmation/Index', []);
    }

    public function add()
    {
        $currentUser = Auth::guard('customer')->user();
        return inertia('customer/wallet-topup-confirmation/Editor', [
            'data' => [
                'parent_name' => $currentUser->parent_name,
                'student_name' => $currentUser->name,
            ]
        ]);
    }

    public function data(Request $request)
    {
        $orderBy = $request->get('order_by', 'datetime');
        $orderType = $request->get('order_type', 'desc');
        $filter = $request->get('filter', []);

        $q = CustomerWalletTransaction::with('customer')
            ->where('customer_id', Auth::guard('customer')->user()->id);

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
        return redirect(route('customer.wallet-topup-confirmation.index'))
            ->with('success', 'Konfirmasi topup telah disimpan.');
    }
}
