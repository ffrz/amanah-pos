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
use Modules\Admin\Http\Requests\Supplier\SaveRequest;
use Modules\Admin\Http\Requests\Supplier\GetDataRequest;
use Modules\Admin\Services\SupplierService;

class SupplierController extends Controller
{
    public function __construct(
        protected SupplierService $supplierService,
    ) {}

    public function index()
    {
        $this->authorize('viewAny', Supplier::class);

        return inertia('supplier/Index');
    }

    public function detail($id = 0)
    {
        $item = $this->supplierService->find($id);

        $this->authorize('view', $item);

        return inertia('supplier/Detail', [
            'data' => $item,
        ]);
    }

    public function data(GetDataRequest $request)
    {
        $this->authorize('viewAny', Supplier::class);

        $items = $this->supplierService->getData($request->validated());

        return JsonResponseHelper::success($items);
    }

    public function duplicate($id)
    {
        $this->authorize('create', Supplier::class);

        $item = $this->supplierService->duplicate($id);

        return inertia('supplier/Editor', [
            'data' => $item,
        ]);
    }

    public function editor($id = 0)
    {
        $item = $this->supplierService->findOrCreate($id);

        $this->authorize($id ? 'update' : 'create', $item);

        return inertia('supplier/Editor', [
            'data' => $item,
        ]);
    }

    public function save(SaveRequest $request)
    {
        $item = $this->supplierService->findOrCreate($request->id);

        $this->authorize($item->id ? 'update' : 'create', $item);

        $this->supplierService->save($item, $request->validated());

        return redirect()
            ->route('admin.supplier.detail', $item->id)
            ->with('success', "Pemasok $item->name telah disimpan.");
    }

    public function delete($id)
    {
        $item = $this->supplierService->find($id);

        $this->authorize('delete', $item);

        $this->supplierService->delete($item);

        return JsonResponseHelper::success($item, "Pemasok $item->name telah dihapus.");
    }
}
