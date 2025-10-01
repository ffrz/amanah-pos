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
use App\Http\Requests\CashierTerminalSaveRequest;
use App\Models\CashierTerminal;
use App\Models\UserActivityLog;
use App\Services\CashierTerminalService;
use App\Services\DocumentVersionService;
use App\Services\UserActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CashierTerminalController extends Controller
{
    public function __construct(
        protected CashierTerminalService $cashierTerminalService,
        protected DocumentVersionService $documentVersionService,
        protected UserActivityLogService $userActivityLogService,
    ) {}

    public function index()
    {
        return inertia('cashier-terminal/Index');
    }

    public function detail($id)
    {
        $data = $this->cashierTerminalService->getTerminal($id);

        return inertia('cashier-terminal/Detail', [
            'data' => $data,
        ]);
    }

    public function duplicate($id)
    {
        $item = $this->cashierTerminalService->getTerminal($id);
        $item->id = null;

        return inertia('cashier-terminal/Editor', [
            'data' => $item,
        ]);
    }

    public function editor($id = 0)
    {
        $data = $id ? $this->cashierTerminalService->getTerminal($id) : new CashierTerminal(['active' => true]);

        $financeAccounts = $this->cashierTerminalService->getAvailableFinanceAccounts($id);

        return inertia('cashier-terminal/Editor', [
            'data' => $data,
            'finance_accounts' => $financeAccounts
        ]);
    }

    /**
     * Mengambil data dengan pagination.
     */
    public function getData(Request $request)
    {
        $filters = $request->only(['search', 'status']);
        $orderBy = $request->get('order_by', 'name');
        $orderType = $request->get('order_type', 'asc');
        $perPage = $request->get('per_page', 10);

        $items = $this->cashierTerminalService->getData($filters, $orderBy, $orderType, $perPage);

        return JsonResponseHelper::success($items);
    }

    /**
     * Menyimpan data (Create/Update).
     */
    public function save(CashierTerminalSaveRequest $request)
    {
        $validated = $request->validated();

        $terminal = $request->id ? CashierTerminal::findOrFail($request->id) : new CashierTerminal();

        $terminal->fill($validated);

        $dirtyAttributes = $terminal->getDirty();

        if (empty($dirtyAttributes)) {
            return redirect(route('admin.cashier-terminal.index'))
                ->with('success', "Tidak ada perubahan data.");
        }

        try {
            DB::beginTransaction();

            if ($validated['finance_account_id'] === 'new') {
                $financeAccount = $this->cashierTerminalService->createAutoCashierAccount($validated['name']);
                $terminal->finance_account_id = $financeAccount->id;
            } else {
                $terminal->finance_account_id = $validated['finance_account_id'];
            }

            $this->cashierTerminalService->save($terminal);
            $this->documentVersionService->createVersion($terminal);

            if (!$request->id) {
                $this->userActivityLogService->log(
                    UserActivityLog::Category_Cashier,
                    UserActivityLog::Name_CashierTerminal_Create,
                    "Terminal kasir $terminal->name telah dibuat.",
                    $dirtyAttributes,
                );
            } else {
                $this->userActivityLogService->log(
                    UserActivityLog::Category_Cashier,
                    UserActivityLog::Name_CashierTerminal_Delete,
                    "Terminal kasir $terminal->name telah diperbarui.",
                    $dirtyAttributes,
                );
            }

            DB::commit();

            return redirect(route('admin.cashier-terminal.index'))
                ->with('success', "Terminal Kasir {$terminal->name} telah disimpan.");
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error("Gagal menyimpan terminal kasir ID: $terminal->id", $ex);
        }

        return redirect()->back()->withInput()
            ->with('error', "Terdapat kesalahan saat menyimpan.");
    }

    /**
     * Menghapus data.
     */
    public function delete($id)
    {
        $item = CashierTerminal::find($id);

        try {
            DB::beginTransaction();

            $item = $item->delete();

            $this->userActivityLogService->log(
                UserActivityLog::Category_Cashier,
                UserActivityLog::Name_CashierTerminal_Delete,
                "Terminal kasir $item->name telah dihapus.",
                $item->getAttributes(),
            );

            DB::commit();
            
            return JsonResponseHelper::success($item, "Terminal Kasir {$item->name} telah dihapus.");
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error("Gagal menghapus terminal ID: $item->id", $ex);
        }

        return JsonResponseHelper::error("Gagal menghapus terminal kasir {$item->name}.");
    }
}
