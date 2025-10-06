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

use App\Helpers\ImageUploaderHelper;
use App\Helpers\JsonResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\FinanceTransaction;
use Modules\Admin\Services\CommonDataService;
use Modules\Admin\Services\FinanceTransactionService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Admin\Http\Requests\FinanceTransaction\GetDataRequest;
use Modules\Admin\Http\Requests\FinanceTransaction\SaveRequest;

class FinanceTransactionController extends Controller
{
    public function __construct(
        protected CommonDataService $commonDataService,
        protected FinanceTransactionService $financeTransactionService
    ) {}

    public function index()
    {
        $this->authorize('viewAny', FinanceTransaction::class);

        return inertia('finance-transaction/Index', [
            'accounts' => $this->commonDataService->getFinanceAccounts()
        ]);
    }

    public function detail($id)
    {
        $item = $this->financeTransactionService->find($id);

        $this->authorize('view', $item);

        return inertia('finance-transaction/Detail', [
            'data' => $item
        ]);
    }

    public function data(GetDataRequest $request)
    {
        $this->authorize('viewAny', FinanceTransaction::class);

        $items = $this->financeTransactionService->getData($request->validated());

        return JsonResponseHelper::success($items);
    }

    public function editor($id = 0)
    {
        $item = $this->financeTransactionService->findOrCreate($id);

        $this->authorize($id ? 'update' : 'create', $item);

        return inertia('finance-transaction/Editor', [
            'data' => $item,
            'accounts' => $this->commonDataService->getFinanceAccounts()
        ]);
    }

    public function save(SaveRequest $request)
    {

        $this->authorize('create', FinanceTransaction::class);

        $item = $this->financeTransactionService->save($request->validated(), $request->file('image'));

        return redirect(route('admin.finance-transaction.index'))
            ->with('success', "Transaksi $item->formatted_id telah disimpan.");
    }

    public function delete($id)
    {

        $item = $this->financeTransactionService->find($id);

        $this->authorize('delete', $item);

        $this->financeTransactionService->delete($item);

        return JsonResponseHelper::success($item, "Transaksi #$item->formatted_id telah dihapus.");
    }
}
