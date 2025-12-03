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
use App\Models\SupplierWalletTransaction;
use Modules\Admin\Services\CommonDataService;
use Modules\Admin\Services\SupplierWalletTransactionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Admin\Http\Requests\SupplierWalletTransaction\GetDataRequest;
use Modules\Admin\Http\Requests\SupplierWalletTransaction\SaveRequest;
use Modules\Admin\Http\Requests\SupplierWalletTransaction\AdjustmentRequest;

class SupplierWalletTransactionController extends Controller
{

    public function __construct(
        protected CommonDataService $commonDataService,
        protected SupplierWalletTransactionService $service
    ) {}

    public function index()
    {
        $this->authorize('viewAny', SupplierWalletTransaction::class);

        return inertia('supplier-wallet-transaction/Index', []);
    }

    public function detail($id)
    {
        $item = $this->service->find($id, [
            'supplier:id,code,name,wallet_balance',
            'financeAccount:id,name,balance'
        ]);

        $this->authorize('view', $item);

        return inertia('supplier-wallet-transaction/Detail', [
            'data' => $item
        ]);
    }

    public function data(GetDataRequest $request)
    {
        $this->authorize('viewAny', SupplierWalletTransaction::class);

        $items = $this->service->getData($request->validated())->withQueryString();

        return JsonResponseHelper::success($items);
    }

    public function editor($id = 0)
    {
        $item = $this->service->findOrCreate($id);

        $this->authorize($id ? 'update' : 'create', $item);

        return inertia('supplier-wallet-transaction/Editor', [
            'data' => $item,
            'suppliers' => $this->commonDataService->getSuppliers(),
            'finance_accounts' => $this->commonDataService->getFinanceAccounts(),
        ]);
    }

    public function save(SaveRequest $request)
    {
        $item = $this->service->findOrCreate($request->id);

        $this->authorize($request->id ? 'update' : 'create', $item);

        $this->service->save($item, $request->validated(), $request->file('image'));

        return redirect(route('admin.supplier-wallet-transaction.index'))
            ->with('success', "Transaksi $item->code telah disimpan.");
    }

    public function adjustment(AdjustmentRequest $request)
    {
        if ($request->getMethod() === 'GET') {
            return inertia('supplier-wallet-transaction/Adjustment', [
                'data' => [],
                'suppliers' => $this->commonDataService->getSuppliers()
            ]);
        }

        $item = $this->service->adjustBalance($request->validated());

        return redirect(route('admin.supplier-wallet-transaction.index'))
            ->with('success', "Penyesuaian saldo {$item->supplier->name} telah disimpan.");
    }

    public function delete($id)
    {
        $item = $this->service->find($id);

        $this->authorize('delete', $item);

        $this->service->delete($item);

        return JsonResponseHelper::success(
            $item,
            "Transaksi #$item->code telah dihapus."
        );
    }
}
