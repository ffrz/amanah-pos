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

use App\Helpers\JsonResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        return inertia('supplier/Index');
    }

    public function detail($id = 0)
    {
        return inertia('supplier/Detail', [
            'data' => Supplier::findOrFail($id),
        ]);
    }

    public function data(Request $request)
    {
        $orderBy = $request->get('order_by', 'name');
        $orderType = $request->get('order_type', 'asc');
        $filter = $request->get('filter', []);

        $q = Supplier::query();

        if (!empty($filter['search'])) {
            $q->where(function ($q) use ($filter) {
                $q->where('name', 'like', '%' . $filter['search'] . '%');
                $q->orWhere('phone', 'like', '%' . $filter['search'] . '%');
                $q->orWhere('address', 'like', '%' . $filter['search'] . '%');
            });
        }

        if (!empty($filter['status']) && ($filter['status'] == 'active' || $filter['status'] == 'inactive')) {
            $q->where('active', '=', $filter['status'] == 'active' ? true : false);
        }

        $q->orderBy($orderBy, $orderType);

        $items = $q->paginate($request->get('per_page', 10))->withQueryString();

        return response()->json($items);
    }

    public function duplicate($id)
    {
        $item = Supplier::findOrFail($id);
        $item->id = null;
        $item->created_at = null;
        return inertia('supplier/Editor', [
            'data' => $item,
        ]);
    }

    public function editor($id = 0)
    {
        $item = $id ? Supplier::findOrFail($id) : new Supplier(['active' => true]);
        return inertia('supplier/Editor', [
            'data' => $item,
        ]);
    }

    public function save(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|max:255',
            'phone' => 'nullable|max:100',
            'bank_account_number' => 'nullable|max:40',
            'active' => 'required|boolean',
            'address' => 'nullable|max:200',
            'return_address' => 'nullable|max:200',
        ]);

        $item = !$request->filled('id') ? new Supplier() : Supplier::findOrFail($request->post('id'));
        $validated['phone'] = $validated['phone'] ?? '';
        $validated['address'] = $validated['address'] ?? '';
        $validated['bank_account_number'] = $validated['bank_account_number'] ?? '';
        $validated['return_address'] = $validated['return_address'] ?? '';
        $item->fill($validated)->save();

        return JsonResponseHelper::success($item, "Supplier $item->name telah disimpan.");
    }

    public function delete($id)
    {
        $item = Supplier::findOrFail($id);
        try {
            $item->delete();
        } catch (Exception $ex) {
            return JsonResponseHelper::error('Gagal menghapus supplier', 500, $ex);
        }

        return JsonResponseHelper::success($item, "Supplier $item->name telah dihapus.");
    }
}
