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
use App\Models\OperationalCost;
use Modules\Admin\Services\CommonDataService;
use Modules\Admin\Services\FinanceTransactionService;
use Modules\Admin\Http\Requests\OperationalCost\SaveRequest;
use Modules\Admin\Http\Requests\OperationalCost\GetDataRequest;
use Modules\Admin\Services\OperationalCostService;

use Illuminate\Support\Facades\Log;

class OperationalCostController extends Controller
{
    public function __construct(
        protected CommonDataService $commonDataService,
        protected FinanceTransactionService $financeTransactionService,
        protected OperationalCostService $operationalCostService,
    ) {}

    public function index()
    {
        $this->authorize('viewAny', OperationalCost::class);

        return inertia('operational-cost/Index', [
            'categories' => $this->commonDataService->getOperationalCategories(),
            'finance_accounts' => $this->commonDataService->getFinanceAccounts(),
        ]);
    }

    public function detail($id)
    {
        $item = $this->operationalCostService->find($id);
        $this->authorize('view', $item);
        return inertia('operational-cost/Detail', [
            'data' => $item
        ]);
    }

    public function data(GetDataRequest $request)
    {
        $items = $this->operationalCostService->getData($request->validated());
        return JsonResponseHelper::success($items);
    }

    public function duplicate($id)
    {
        $item = $this->operationalCostService->duplicate($id);
        $this->authorize("create", $item);
        return $this->renderEditor($item);
    }

    public function editor($id = 0)
    {
        $item = $this->operationalCostService->findOrCreate($id);
        $this->authorize($id ? "update" : "create", $id ? $item : OperationalCost::class);
        return $this->renderEditor($item);
    }

    private function renderEditor($item)
    {
        return inertia('operational-cost/Editor', [
            'data' => $item,
            'categories' => $this->commonDataService->getOperationalCategories(),
            'finance_accounts' => $this->commonDataService->getFinanceAccounts(),
        ]);
    }

    public function save(SaveRequest $request)
    {
        $validated = $request->validated();

        $item = $this->operationalCostService->findOrCreate($request->id);

        if ($request->id) {
            $this->authorize('update', $item);
        } else {
            $this->authorize('create', OperationalCost::class);
        }

        try {
            $item = $this->operationalCostService->save(
                $item,
                $validated,
                $request->hasFile('image') ? $request->file('image') : null
            );

            if (!$item) {
                return redirect()->back()
                    ->with('info', "Tidak terdeteksi perubahan data.");
            }

            return redirect()
                ->route('admin.operational-cost.index')
                ->with("success", "Biaya operasional $item->description telah disimpan");
        } catch (\Exception $ex) {
            Log::error("Gagal menyimpan biaya operasional ID: $request->id, {$ex->getMessage()}", ['exception' => $ex]);
        }

        return redirect()->back()->withInput()->with('error', "Gagal menyimpan biaya operasional.");
    }


    public function delete($id)
    {
        $item = $this->operationalCostService->find($id);

        $this->authorize('delete', $item);

        try {
            $this->operationalCostService->delete($item);

            return JsonResponseHelper::success(
                $item,
                "Biaya operasional $item->description telah dihapus."
            );
        } catch (\Throwable $ex) {
            Log::error("Gagal menghapus Biaya Operasional ID: $id. $ex->getMessage()", ['exception' => $ex]);
        }

        return JsonResponseHelper::error("Gagal menghapus rekaman.", 500, $ex);
    }
}
