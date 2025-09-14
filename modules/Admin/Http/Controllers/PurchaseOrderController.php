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
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PurchaseOrderController extends Controller
{
    protected function _suppliers()
    {
        return Supplier::where('active', '=', true)->orderBy('name', 'asc')->get();
    }

    public function index()
    {
        return inertia('purchase-order/Index', [
            'suppliers' => $this->_suppliers(),
        ]);
    }

    public function data(Request $request)
    {
        $orderBy = $request->get('order_by', 'datetime');
        $orderType = $request->get('order_type', 'desc');
        $filter = $request->get('filter', []);

        $q = PurchaseOrder::with('supplier');

        if (!empty($filter['search'])) {
            $q->where(function ($q) use ($filter) {
                $q->orWhere('notes', 'like', '%' . $filter['search'] . '%');
            });
        }


        // Tambahan filter tahun
        if (!empty($filter['year']) && $filter['year'] !== 'null') {
            $q->whereYear('datetime', $filter['year']);

            if (!empty($filter['month']) && $filter['month'] !== 'null') {
                $q->whereMonth('datetime', $filter['month']);
            }
        }

        $q->orderBy($orderBy, $orderType);

        $items = $q->paginate($request->get('per_page', 10))->withQueryString();

        return response()->json($items);
    }

    public function duplicate($id)
    {
        allowed_roles([User::Role_Admin]);
        $item = PurchaseOrder::findOrFail($id);
        $item->id = null;

        return inertia('purchase-order/Editor', [
            'data' => $item,
            'suppliers' => $this->_suppliers(),
        ]);
    }

    public function editor($id = 0)
    {
        allowed_roles([User::Role_Admin]);
        $item = $id ? PurchaseOrder::findOrFail($id) : new PurchaseOrder([
            'datetime' => Carbon::now(),
            'status' => PurchaseOrder::Status_Draft,
            'payment_status' => PurchaseOrder::PaymentStatus_Unpaid,
            'delivery_status' => PurchaseOrder::DeliveryStatus_NotSent,
        ]);
        return inertia('purchase-order/Editor', [
            'data' => $item,
            'suppliers' => $this->_suppliers(),
        ]);
    }

    public function save(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'supplier_id' => 'nullable',
            'total' => 'required|numeric|gt:0',
            'notes' => 'nullable|max:1000',
        ]);

        if (!$request->id) {
            $item = new PurchaseOrder();
            $message = 'purchase-order-created';
        } else {
            $item = PurchaseOrder::findOrFail($request->post('id', 0));
            $message = 'purchase-order-updated';
        }

        $validated['notes'] = $validated['notes'] ?? '';

        $item->fill($validated);
        $item->save();

        return redirect(route('admin.purchase-order.index'))
            ->with('success', __("messages.$message", ['description' => $item->description]));
    }

    public function delete($id)
    {
        allowed_roles([User::Role_Admin]);

        $item = PurchaseOrder::findOrFail($id);

        // TODO: hapus PurchaseOrderDetail, hapus StockMovement, hapus payment

        $item->delete();

        return response()->json([
            'message' => "Order pembelian #$item->id telah dihapus."
        ]);
    }
}
