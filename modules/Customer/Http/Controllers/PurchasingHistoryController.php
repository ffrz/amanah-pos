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
use App\Models\SalesOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PurchasingHistoryController extends Controller
{

    public function index()
    {
        return inertia('purchasing-history/Index', []);
    }

    public function data(Request $request)
    {
        $orderBy = $request->get('order_by', 'datetime');
        $orderType = $request->get('order_type', 'desc');
        $filter = $request->get('filter', []);

        $q = SalesOrder::with(['customer', 'details'])
            ->where('customer_id', Auth::guard('customer')->user()->id)
            ->where('status', '=', 'closed');

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

    public function detail($id)
    {
        $order = SalesOrder::with(['customer', 'details', 'payments', 'cashier'])->findOrFail($id);

        // authorize
        if ($order->customer_id !== Auth::guard('customer')->user()->id) {
            return abort(403);
        }

        return inertia('purchasing-history/Detail', [
            'data' => $order
        ]);
    }
}
