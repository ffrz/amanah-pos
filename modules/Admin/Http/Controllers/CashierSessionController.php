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
use App\Models\CashierSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Admin\Http\Requests\CashierSession\GetDataRequest;
use Modules\Admin\Http\Requests\CashierSession\CloseRequest;
use Modules\Admin\Http\Requests\CashierSession\OpenRequest;
use Modules\Admin\Services\CashierSessionService;
use Modules\Admin\Services\CashierTerminalService;

class CashierSessionController extends Controller
{
    public function __construct(
        protected CashierSessionService $cashierSessionService,
        protected CashierTerminalService $cashierTerminalService,
    ) {}

    public function index()
    {
        $this->authorize('viewAny', CashierSession::class);

        return inertia('cashier-session/Index');
    }

    public function detail($id = 0)
    {
        $item = $this->cashierSessionService->find($id);

        $this->authorize('view', $item);

        return inertia('cashier-session/Detail', [
            'data' => $item,
        ]);
    }

    public function data(GetDataRequest $request)
    {
        $this->authorize('viewAny', CashierSession::class);

        $items = $this->cashierSessionService->getData($request->validated());

        return JsonResponseHelper::success($items);
    }

    public function open(OpenRequest $request)
    {
        $activeSession = $this->cashierSessionService->getActiveSession();

        if ($activeSession) {
            return redirect(route('admin.cashier-session.detail', $activeSession->id))
                ->with('warning', 'Anda sudah memiliki sesi kasir aktif.');
        }

        $this->authorize('create', CashierSession::class);

        if ($request->method() == Request::METHOD_POST) {
            $session = $this->cashierSessionService->open($request->validated(), Auth::user());

            return redirect(route('admin.cashier-session.detail', $session->id))
                ->with('success', "Sesi kasir telah dimulai.");
        }

        return inertia('cashier-session/Open', [
            'data' => new CashierSession(),
            'cashier_terminals' => $this->cashierTerminalService->getAvailableCashierTerminals(),
        ]);
    }

    public function close(CloseRequest $request, $id)
    {
        $item = $this->cashierSessionService->find($id);

        if ($item->is_closed) {
            return redirect()->back()->with('warning', 'Sesi yang telah selesai tidak bisa ditutup!');
        }

        $this->authorize('update', $item);

        $item->closing_balance = $item->cashierTerminal->financeAccount->balance;

        if ($request->method() == Request::METHOD_POST) {
            $this->cashierSessionService->close($item, $request->validated());

            return redirect(route('admin.cashier-session.detail', $item->id))
                ->with('success', "Sesi kasir {$item->id} telah ditutup.");
        }

        return inertia('cashier-session/Close', [
            'data' => $item,
        ]);
    }

    public function delete($id)
    {
        $item = $this->cashierSessionService->find($id);

        $this->authorize('delete', $item);

        $item = $this->cashierSessionService->delete($item);

        return JsonResponseHelper::success($item, "Sesi kasir #$item->id telah dihapus.");
    }
}
