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

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\StockMovement;
use Illuminate\Http\Request;

class StockMovementController extends Controller
{
    public function index(Request $request)
    {
        return inertia('stock-movement/Index');
    }

    public function data(Request $request)
    {
        $orderBy = $request->get('order_by', 'created_at');
        $orderType = $request->get('order_type', 'desc');
        $filter = $request->get('filter', []);

        $q = StockMovement::with(['creator', 'product']);

        if ($request->has('product_id')) {
            $q->where('product_id', $request->get('product_id', 0));
        }

        if (!empty($filter['search'])) {
            $q->where(function ($q) use ($filter) {
                $q->orWhere('notes', 'like', '%' . $filter['search'] . '%');
            });

            $q->orWhereHas('product', function ($query) use ($filter) {
                $query->where('name', 'like', '%' . $filter['search'] . '%');
            });
        }

        if (!empty($filter['ref_type']) && $filter['ref_type'] != 'all') {
            $q->where('ref_type', $filter['ref_type']);
        }

        if (!empty($filter['year']) && $filter['year'] !== 'all') {
            $q->whereYear('created_at', $filter['year']);

            if (!empty($filter['month']) && $filter['month'] !== 'all') {
                $q->whereMonth('created_at', $filter['month']);
            }
        }

        $q->orderBy($orderBy, $orderType);

        $items = $q->paginate($request->get('per_page', 10))->withQueryString();

        return response()->json($items);
    }
}
