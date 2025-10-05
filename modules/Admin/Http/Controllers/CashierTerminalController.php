<?php

/**
 * Proprietary Software / Perangkat Lunak Proprietary
 * Copyright (c) 2025 Fahmi Fauzi Rahman. All rights reserved.
 * * EN: Unauthorized use, copying, modification, or distribution is prohibited.
 * ID: Penggunaan, penyalinan, modifikasi, atau distribusi tanpa izin dilarang.
 * * Controller ini direfactor untuk memindahkan logika bisnis ke CashierTerminalService.
 * Ini adalah Controller yang tipis (Thin Controller).
 */

namespace Modules\Admin\Http\Controllers;

use App\Helpers\JsonResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\CashierTerminal;
use Modules\Admin\Services\CashierTerminalService;
use Modules\Admin\Http\Requests\CashierTerminal\SaveRequest;
use Modules\Admin\Http\Requests\CashierTerminal\GetDataRequest;

class CashierTerminalController extends Controller
{
    public function __construct(
        protected CashierTerminalService $cashierTerminalService,
    ) {}

    public function index()
    {
        $this->authorize('viewAny', CashierTerminal::class);

        return inertia('cashier-terminal/Index');
    }

    public function detail($id)
    {
        $item = $this->cashierTerminalService->find($id);

        $this->authorize('view', $item);

        return inertia('cashier-terminal/Detail', [
            'data' => $item,
        ]);
    }

    public function duplicate($id)
    {
        $this->authorize('create', CashierTerminal::class);

        $item = $this->cashierTerminalService->duplicate($id);

        return inertia('cashier-terminal/Editor', [
            'data' => $item,
        ]);
    }

    public function editor($id = 0)
    {
        $item = $this->cashierTerminalService->findOrCreate($id);

        $this->authorize($id ? 'update' : 'create', $item);

        $financeAccounts = $this->cashierTerminalService->getAvailableFinanceAccounts($id);

        return inertia('cashier-terminal/Editor', [
            'data' => $item,
            'finance_accounts' => $financeAccounts
        ]);
    }

    /**
     * Mengambil data dengan pagination.
     */
    public function data(GetDataRequest $request)
    {
        $this->authorize('viewAny', CashierTerminal::class);

        $items = $this->cashierTerminalService->getData($request->validated());

        return JsonResponseHelper::success($items);
    }

    /**
     * Menyimpan data (Create/Update).
     */
    public function save(SaveRequest $request)
    {
        $item = $this->cashierTerminalService->findOrCreate($request->id);

        $this->authorize($request->id ? 'update' : 'create', $item);

        $this->cashierTerminalService->save($item, $request->validated());

        return redirect(route('admin.cashier-terminal.index'))
            ->with('success', "Terminal Kasir {$item->name} telah disimpan.");
    }

    /**
     * Menghapus data.
     */
    public function delete($id)
    {
        $item = CashierTerminal::find($id);

        $this->authorize('delete', $item);

        $this->cashierTerminalService->delete($item);

        return JsonResponseHelper::success($item, "Terminal Kasir {$item->name} telah dihapus.");
    }
}
