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
use App\Models\FinanceAccount;
use Illuminate\Support\Facades\Log;
use Modules\Admin\Http\Requests\FinanceAccount\GetDataRequest;
use Modules\Admin\Http\Requests\FinanceAccount\SaveRequest;
use Modules\Admin\Services\FinanceAccountService;

class FinanceAccountController extends Controller
{
    public function __construct(protected FinanceAccountService $financeAccountService) {}

    public function index()
    {
        $this->authorize('viewAny', FinanceAccount::class);

        $balance = $this->financeAccountService->getTotalActiveAccountBalance();

        return inertia('finance-account/Index', [
            'totalBalance' => $balance,
        ]);
    }

    public function detail($id = 0)
    {
        $item = $this->financeAccountService->find($id);

        $this->authorize('view', $item);

        return inertia('finance-account/Detail', [
            'data' => $item,
        ]);
    }

    /**
     * Menggunakan Form Request untuk validasi dan penyiapan data query.
     */
    public function data(GetDataRequest $request)
    {
        $this->authorize('viewAny', FinanceAccount::class);

        $items = $this->financeAccountService->getData(
            $request->validated()
        );

        return JsonResponseHelper::success($items);
    }

    public function duplicate($id)
    {
        $this->authorize('create', FinanceAccount::class);

        $item = $this->financeAccountService->duplicate($id);

        return inertia('finance-account/Editor', [
            'data' => $item,
        ]);
    }

    public function editor($id = 0)
    {
        $item = $this->financeAccountService->findOrCreate($id);

        $this->authorize($id ? 'update' : 'create', $item);

        return inertia('finance-account/Editor', [
            'data' => $item,
        ]);
    }

    /**
     * Menggunakan Form Request untuk validasi input POST/PUT.
     */
    public function save(SaveRequest $request)
    {
        $item = $this->financeAccountService->findOrCreate($request->id);

        $this->authorize($request->id ? 'update' : 'create', $item);

        $item = $this->financeAccountService->save(
            $item,
            $request->validated()
        );

        return redirect(route('admin.finance-account.index'))
            ->with('success', "Akun $item->name telah disimpan.");
    }

    public function delete($id)
    {
        $item = $this->financeAccountService->find($id);

        $this->authorize('delete', $item);

        $item = $this->financeAccountService->delete($item);

        return JsonResponseHelper::success(
            $item,
            "Akun kas $item->name telah dihapus."
        );
    }
}
