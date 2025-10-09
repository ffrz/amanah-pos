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
use App\Models\Customer;
use App\Models\CustomerWalletTransaction;
use Modules\Admin\Services\CommonDataService;
use Modules\Admin\Services\CustomerWalletTransactionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Admin\Http\Requests\CustomerWalletTransaction\GetDataRequest;
use Modules\Admin\Http\Requests\CustomerWalletTransaction\SaveRequest;
use Modules\Admin\Http\Requests\CustomerWalletTransaction\AdjustmentRequest;

class CustomerWalletTransactionController extends Controller
{

    public function __construct(
        protected CommonDataService $commonDataService,
        protected CustomerWalletTransactionService $service
    ) {}

    public function index()
    {
        $this->authorize('viewAny', CustomerWalletTransaction::class);

        return inertia('customer-wallet-transaction/Index', []);
    }

    public function detail($id)
    {
        $item = $this->service->find($id);

        $this->authorize('view', $item);

        return inertia('customer-wallet-transaction/Detail', [
            'data' => $item
        ]);
    }

    public function data(GetDataRequest $request)
    {
        $this->authorize('viewAny', CustomerWalletTransaction::class);

        $items = $this->service->getData($request->validated())->withQueryString();

        return JsonResponseHelper::success($items);
    }

    public function editor($id = 0)
    {
        $item = $this->service->findOrCreate($id);

        $this->authorize($id ? 'update' : 'create', $item);

        return inertia('customer-wallet-transaction/Editor', [
            'data' => $item,
            'customers' => $this->commonDataService->getCustomers(),
            'finance_accounts' => $this->commonDataService->getFinanceAccounts(),
        ]);
    }

    public function save(SaveRequest $request)
    {
        $item = $this->service->findOrCreate($request->id);

        $this->authorize($request->id ? 'update' : 'create', $item);

        $this->service->save($item, $request->validated(), $request->file('image'));

        return redirect(route('admin.customer-wallet-transaction.index'))
            ->with('success', "Transaksi $item->formatted_id telah disimpan.");
    }

    public function adjustment(AdjustmentRequest $request)
    {
        if ($request->getMethod() === 'GET') {
            return inertia('customer-wallet-transaction/Adjustment', [
                'data' => [],
                'customers' => $this->commonDataService->getCustomers()
            ]);
        }

        $item = $this->service->adjustBalance($request->validated());

        return redirect(route('admin.customer-wallet-transaction.index'))
            ->with('success', "Penyesuaian saldo {$item->customer->name} telah disimpan.");
    }

    public function delete($id)
    {
        $item = $this->service->find($id);

        $this->authorize('delete', $item);

        $this->service->delete($item);

        return JsonResponseHelper::success(
            $item,
            "Transaksi #$item->formatted_id telah dihapus."
        );
    }
}
