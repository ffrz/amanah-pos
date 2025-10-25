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
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Modules\Admin\Http\Requests\StockAdjustment\GetDataRequest;
use Modules\Admin\Http\Requests\StockAdjustment\SaveRequest;
use Modules\Admin\Http\Requests\StockAdjustment\CreateRequest;
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

    public function create(CreateRequest $request)
    {
        $this->authorize('create', StockAdjustment::class);

        if ($request->method() == 'POST') {
            $item = $this->stockAdjustmentService->create($request->validated());

            return redirect(route('admin.stock-adjustment.editor', [
                'id' => $item->id
            ]))->with([
                'message' => "Penyesuaian stok $item->code telah dibuat."
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
        $details = $item->details;
        return inertia('stock-adjustment/Editor', [
            'item' => $item,
            'details' => $details
        ]);
    }

    public function save(SaveRequest $request)
    {
        $action_status_map = [
            'save' => StockAdjustment::Status_Draft,
            'close' => StockAdjustment::Status_Closed,
            'cancel' => StockAdjustment::Status_Cancelled,
        ];

        $validated = $request->validated();

        $validated['status'] = $action_status_map[$validated['action']];

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

        return JsonResponseHelper::success($item, "Penyesuaian stock $item->code telah dihapus.");
    }

    public function printStockCard(Request $request, $id)
    {
        $item = $this->stockAdjustmentService->find($id);

        $this->authorize('view', $item);

        $details = $this->stockAdjustmentService->getDetails($id);

        $template = 'modules.admin.pages.stock-adjustment.print-stock-card';

        if ($request->get('output') == 'pdf') {
            $pdf = Pdf::loadView($template, [
                'item' => $item,
                'details' => $details,
                'pdf'  => true,
            ])
                ->setPaper('a4', 'portrait')
                ->setOption('isHtml5ParserEnabled', true)
                ->setOption('isPhpEnabled', true);
            return $pdf->download(env('APP_NAME') . ' - KARTU STOK - ' . $item->code . '.pdf');
        }

        return view($template, [
            'item' => $item,
            'details' => $details,
        ]);
    }

    public function print(Request $request, $id)
    {
        $item = $this->stockAdjustmentService->find($id);

        $this->authorize('view', $item);

        $details = $item->details;

        $template = 'modules.admin.pages.stock-adjustment.print';

        if ($request->get('output') == 'pdf') {
            $pdf = Pdf::loadView($template, [
                'item' => $item,
                'details' => $details,
                'pdf'  => true,
            ])
                ->setPaper('a4', 'portrait')
                ->setOption('isHtml5ParserEnabled', true)
                ->setOption('isPhpEnabled', true);
            return $pdf->download(env('APP_NAME') . ' - PENYESUAIAN STOK - ' . $item->code . '.pdf');
        }

        return view($template, [
            'item' => $item,
            'details' => $details,
        ]);
    }
}
