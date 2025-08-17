<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\StockMovement;
use Illuminate\Http\Request;

class StockMovementController extends Controller
{
    public function data(Request $request)
    {
        $orderBy = $request->get('order_by', 'date');
        $orderType = $request->get('order_type', 'desc');
        $filter = $request->get('filter', []);

        $q = StockMovement::with(['createdBy']);
        $q->where('product_id', $request->get('product_id', 0));

        if (!empty($filter['search'])) {
            $q->where(function ($q) use ($filter) {
                // $q->where('name', 'like', '%' . $filter['search'] . '%');
                // $q->orWhere('description', 'like', '%' . $filter['search'] . '%');
                // $q->orWhere('notes', 'like', '%' . $filter['search'] . '%');
            });
        }

        // if (!empty($filter['type']) && $filter['type'] != 'all') {
        //     $q->where('type', '=', $filter['type']);
        //     if ($filter['type'] == Product::Type_Stocked) {
        //         if (!empty($filter['stock_status']) && $filter['stock_status'] != 'all') {
        //             if ($filter['stock_status'] == 'low') {
        //                 $q->whereColumn('stock', '<', 'min_stock');
        //                 $q->where('stock', '!=', 0);
        //             } elseif ($filter['stock_status'] == 'out') {
        //                 $q->where('stock', '=', 0);
        //             } elseif ($filter['stock_status'] == 'over') {
        //                 $q->whereColumn('stock', '>', 'max_stock');
        //             } elseif ($filter['stock_status'] == 'ready') {
        //                 $q->where('stock', '>', 0);
        //             }
        //         }
        //     }
        // }

        // if (!empty($filter['category_id']) && $filter['category_id'] != 'all') {
        //     $q->where('category_id', '=', $filter['category_id']);
        // }

        // if (!empty($filter['supplier_id']) && $filter['supplier_id'] != 'all') {
        //     $q->where('supplier_id', '=', $filter['supplier_id']);
        // }

        // if (!empty($filter['status']) && ($filter['status'] == 'active' || $filter['status'] == 'inactive')) {
        //     $q->where('active', '=', $filter['status'] == 'active' ? true : false);
        // }

        $q->orderBy($orderBy, $orderType);

        $items = $q->paginate($request->get('per_page', 10))->withQueryString();

        return response()->json($items);
    }
}
