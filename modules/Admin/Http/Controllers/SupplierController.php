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
use App\Services\DocumentVersionService;
use App\Services\UserActivityLogService;
use App\Services\SupplierService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SupplierController extends Controller
{
    public function __construct(
        protected SupplierService $supplierService,
        protected UserActivityLogService $userActivityLogService,
        protected DocumentVersionService $documentVersionService,
    ) {}

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
        $items = $this->supplierService->getData($request);
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
            'phone_1' => 'nullable|max:50',
            'phone_2' => 'nullable|max:50',
            'phone_3' => 'nullable|max:50',
            'bank_account_name_1' => 'nullable|max:50',
            'bank_account_number_1' => 'nullable|max:50',
            'bank_account_holder_1' => 'nullable|max:100',
            'bank_account_name_2' => 'nullable|max:50',
            'bank_account_number_2' => 'nullable|max:50',
            'bank_account_holder_2' => 'nullable|max:100',
            'active' => 'required|boolean',
            'address' => 'nullable|max:200',
            'return_address' => 'nullable|max:200',
            'url_1' => 'nullable|max:512',
            'url_2' => 'nullable|max:512',
            'notes' => 'nullable|max:1000',
        ]);

        $item = !$request->filled('id') ? new Supplier() : Supplier::findOrFail($request->post('id'));
        $oldData = $item->toArray();
        $isCreating = !$item->exists;

        $tempItem = $item->replicate()->fill($validated);
        if ($item->exists && empty($tempItem->getDirty())) {
            return redirect()
                ->route('admin.supplier.index')
                ->with('success', "Tidak terdeteksi perubahan data.");
        }

        $savedItem = $item;

        try {
            DB::beginTransaction();

            $savedItem = $this->supplierService->save($validated, $item);

            $this->documentVersionService->createVersion($savedItem);

            if ($isCreating) {
                $this->userActivityLogService->log(
                    UserActivityLog::Category_Supplier,
                    UserActivityLog::Name_Supplier_Create,
                    "Pemasok '$savedItem->name' telah dibuat.",
                    $savedItem->toArray(),
                );
            } else {
                $this->userActivityLogService->log(
                    UserActivityLog::Category_Supplier,
                    UserActivityLog::Name_Supplier_Update,
                    "Pemasok '$savedItem->name' telah diperbarui.",
                    [
                        'new_data' => $savedItem->toArray(),
                        'old_data' => $oldData,
                    ]
                );
            }

            DB::commit();

            return redirect()
                ->route('admin.supplier.index')
                ->with('success', "Pemasok $savedItem->name telah disimpan.");
        } catch (Exception $ex) {
            DB::rollBack();

            $itemId = isset($savedItem) ? $savedItem->id : 'baru';
            Log::error("Gagal menyimpan pemasok ID: $itemId", ['exception' => $ex]);

            $itemName = isset($savedItem) ? $savedItem->name : 'baru';
            return redirect()->back()->withInput()->with('error', "Gagal menyimpan pemasok $itemName.");
        }
    }

    public function delete($id)
    {
        $item = Supplier::findOrFail($id);
        $oldData = $item->toArray();
        $itemName = $item->name;

        try {
            DB::beginTransaction();

            $this->supplierService->delete($item);

            $this->userActivityLogService->log(
                UserActivityLog::Category_Supplier,
                UserActivityLog::Name_Supplier_Delete,
                "Pemasok '$itemName' telah dihapus.",
                $oldData,
            );

            DB::commit();

            return JsonResponseHelper::success(null, "Pemasok $itemName telah dihapus.");
        } catch (Exception $ex) {
            DB::rollBack();
            Log::error("Gagal menghapus pemasok ID: $id", ['exception' => $ex]);
        }

        return JsonResponseHelper::error('Gagal menghapus pemasok.');
    }
}
