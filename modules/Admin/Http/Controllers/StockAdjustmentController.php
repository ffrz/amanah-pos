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
use App\Models\StockAdjustment;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Modules\Admin\Http\Requests\StockAdjustment\GetDataRequest;
use Modules\Admin\Http\Requests\StockAdjustment\SaveRequest;
use Modules\Admin\Services\CommonDataService;
use Modules\Admin\Services\StockAdjustmentService;

class StockAdjustmentController extends Controller
{
    public function __construct(
        protected StockAdjustmentService $stockAdjustmentService,
        protected CommonDataService $commonDataService
    ) {}

    public function index()
    {
        $this->authorize('viewAny', StockAdjustment::class);

        return inertia('stock-adjustment/Index');
    }

    public function data(GetDataRequest $request)
    {
        $this->authorize('viewAny', StockAdjustment::class);

        $items = $this->stockAdjustmentService->getData($request->validated())->withQueryString();

        return JsonResponseHelper::success($items);
    }

    public function detail($id = 0)
    {
        $item = $this->stockAdjustmentService->find($id);

        $this->authorize('view', $item);

        return inertia('stock-adjustment/Detail', [
            'data' => $item,
            'details' => $item->details
        ]);
    }

    public function create(Request $request)
    {
        $this->authorize('create', StockAdjustment::class);

        if ($request->method() == 'POST') {
            // TODO: Validate Request, untuk amankan masalah security
            $item = $this->stockAdjustmentService->create([
                'product_ids' => $request->post('product_ids', []),
                'datetime' => $request->post('datetime', date('Y-m-d H:i:s')),
                'type' => $request->post('type', StockAdjustment::Type_StockCorrection),
                'notes' => $request->post('notes', ''),
            ]);

            return redirect(route('admin.stock-adjustment.editor', [
                'id' => $item->id
            ]))->with([
                'message' => __('messages.stock-adjustment-created', ['id' => $item->id])
            ]);
        }

        return inertia('stock-adjustment/Create', [
            'categories' => $this->commonDataService->getProductCategories(),
            'suppliers' => $this->commonDataService->getSuppliers(),
            'products' => $this->stockAdjustmentService->getProducts(),
        ]);
    }

    public function editor($id)
    {
        $item = $this->stockAdjustmentService->find($id);

        $this->authorize('update', $item);

        $details = $this->stockAdjustmentService->getDetails($id);

        return inertia('stock-adjustment/Editor', [
            'item' => $item,
            'details' => $details
        ]);
    }

    public function save(SaveRequest $request)
    {
        $validated = $request->validated();

        $item = $this->stockAdjustmentService->find($request->id);

        $this->authorize('update', $item);

        $this->stockAdjustmentService->save($item, $validated);

        $route = route('admin.stock-adjustment.editor', ['id' => $item->id]);
        if ($validated['action'] === 'cancel') {
            $route = route('admin.stock-adjustment.index');
        } else if ($validated['action'] === 'close') {
            $route = route('admin.stock-adjustment.detail', ['id' => $item->id]);
        }

        return redirect($route)
            ->with(['message' => 'Penyesuaian stok telah disimpan.']);
    }

    public function delete($id)
    {
        $item = $this->stockAdjustmentService->find($id);

        $this->authorize('delete', $item);

        $item = $this->stockAdjustmentService->delete($item);

        return JsonResponseHelper::success($item, "Penyesuaian stock $item->formatted_id telah dihapus.");
    }
}
