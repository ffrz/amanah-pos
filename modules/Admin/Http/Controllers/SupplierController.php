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
use App\Models\UserActivityLog;
use App\Services\UserActivityLogService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SupplierController extends Controller
{
    /**
     * @var UserActivityLogService
     */
    protected $userActivityLogService;

    public function __construct(UserActivityLogService $userActivityLogService)
    {
        $this->userActivityLogService = $userActivityLogService;
    }

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
            'name' => 'required|string|max:50|unique:suppliers,name' . ($request->id ? ',' . $request->id : ''),
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

        $oldData = $item->toArray();
        $item->fill($validated);
        if (empty($item->getDirty())) {
            return redirect()
                ->route('admin.supplier.index')
                ->with('success', "Tidak terdeteksi perubahan data.");
        }

        try {
            DB::beginTransaction();

            $item->save();

            if (!$request->id) {
                $this->userActivityLogService->log(
                    UserActivityLog::Category_Supplier,
                    UserActivityLog::Name_Supplier_Create,
                    "Pemasok '$item->name' telah dibuat.",
                    $item->toArray(),
                );
            } else {
                $this->userActivityLogService->log(
                    UserActivityLog::Category_Supplier,
                    UserActivityLog::Name_Supplier_Update,
                    "Pemasok '$item->name' telah diperbarui.",
                    [
                        'new_data' => $item->toArray(),
                        'old_data' => $oldData,
                    ]
                );
            }

            DB::commit();

            return redirect()
                ->route('admin.supplier.index')
                ->with('success', "Pemasok $item->name telah disimpan.");
        } catch (Exception $ex) {
            DB::rollBack();

            Log::error("Gagal menyimpan pemasok ID: $item->id", ['exception' => $ex]);
        }

        return redirect()->route('admin.supplier.index')->with('error', "Gagal menyimpan pemasok $item->name.");
    }

    public function delete($id)
    {
        $item = Supplier::findOrFail($id);
        try {
            DB::beginTransaction();

            $item->delete();

            $this->userActivityLogService->log(
                UserActivityLog::Category_Supplier,
                UserActivityLog::Name_Supplier_Delete,
                "Pemasok '$item->name' telah dihapus.",
                $item->toArray(),
            );

            DB::commit();

            return JsonResponseHelper::success($item, "Pemasok $item->name telah dihapus.");
        } catch (Exception $ex) {
            DB::rollBack();

            Log::error("Gagal menghapus pemasok ID: $id", ['exception' => $ex]);
        }

        return JsonResponseHelper::error('Gagal menghapus pemasok.');
    }
}
