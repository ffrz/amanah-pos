<?php

/**
 * Proprietary Software / Perangkat Lunak Proprietary
 * Copyright (c) 2025 Fahmi Fauzi Rahman. All rights reserved.
 * 
 * EN: Unauthorized use, copying, modification, or distribution is prohibited.
 * ID: Penggunaan, penyalinan, modifikasi, atau distribusi tanpa izin dilarang.
 * 
 * See the LICENSE file in the project root for full license information.
 * Lihat file LICENSE di root proyek untuk informasi lisensi lengkap.
 * 
 * GitHub: https://github.com/ffrz
 * Email: fahmifauzirahman@gmail.com
 */

namespace Modules\Customer\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CustomerWalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WalletTransactionController extends Controller
{

    public function index()
    {
        return inertia('wallet-transaction/Index', []);
    }

    public function data(Request $request)
    {
        $orderBy = $request->get('order_by', 'id');
        $orderType = $request->get('order_type', 'desc');
        $filter = $request->get('filter', []);

        $q = CustomerWalletTransaction::with('customer')
            ->where('customer_id', Auth::guard('customer')->user()->id);

        if (!empty($filter['search'])) {
            $q->where(function ($q) use ($filter) {
                $q->orWhere('notes', 'like', '%' . $filter['search'] . '%');
            });
        }

        if (!empty($filter['type']) && $filter['type'] !== 'all') {
            $q->where('type', $filter['type']);
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

    public function detail($id)
    {
        $trx = CustomerWalletTransaction::with(['customer'])->findOrFail($id);

        if ($trx->customer_id !== Auth::guard("customer")->user()->id) {
            return abort(403);
        }

        return inertia('wallet-transaction/Detail', [
            'data' => $trx
        ]);
    }
}
