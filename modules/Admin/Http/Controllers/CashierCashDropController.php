<?php

/**
 * Proprietary Software / Perangkat Lunak Proprietary
 * Copyright (c) 2025 Fahmi Fauzi Rahman. All rights reserved.
 * * EN: Unauthorized use, copying, modification, or distribution is prohibited.
 * ID: Penggunaan, penyalinan, modifikasi, atau distribusi tanpa izin dilarang.
 * * See the LICENSE file in the project root for full license information.
 * Lihat file LICENSE di root proyek untuk informasi lisensi lengkap.
 * * GitHub: https://github.com/ffrz
 * Email: fahmifauzirahman@gmail.com
 */

namespace Modules\Admin\Http\Controllers;

use App\Exceptions\BusinessRuleViolationException;
use App\Helpers\JsonResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\CashierCashDrop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Admin\Services\CashierCashDropService;
use Modules\Admin\Services\CommonDataService;
use Modules\Admin\Http\Requests\CashierCashDrop\GetDataRequest;
use Modules\Admin\Http\Requests\CashierCashDrop\StoreRequest;
use Modules\Admin\Http\Requests\CashierCashDrop\ConfirmRequest;
use Modules\Admin\Services\CashierSessionService;

class CashierCashDropController extends Controller
{
    public function __construct(
        protected CashierCashDropService $service,
        protected CommonDataService $commonDataService,
    ) {}

    public function index()
    {
        $this->authorize('viewAny', CashierCashDrop::class);

        return inertia('cashier-cash-drop/Index', [
            'cashiers' => $this->commonDataService->getAllUsers(['id', 'username', 'name']),
            'cashier_terminals' => $this->commonDataService->getAllCashierTerminals(),
        ]);
    }

    public function detail($id)
    {
        $item = $this->service->find($id);

        $this->authorize('view', $item);

        return inertia('cashier-cash-drop/Detail', [
            'data' => $item
        ]);
    }

    public function data(GetDataRequest $request)
    {
        $this->authorize('viewAny', CashierCashDrop::class);

        $items = $this->service->getData($request->validated());

        return JsonResponseHelper::success($items);
    }

    /**
     * Halaman Editor untuk membuat pengajuan baru.
     * Menggantikan method 'editor' pada OperationalCost karena CashDrop sifatnya create-only (tidak diedit setelah submit).
     */
    public function create()
    {
        $this->authorize('create', CashierCashDrop::class);

        $session = app(CashierSessionService::class)->getActiveSession();

        return inertia('cashier-cash-drop/Create', [
            'data' => [
                'cashier_terminal_id' => $session ? $session->cashierTerminal->id : null,
                'cashier_session_id' => $session ? $session->id : null,
                'source_finance_account_id' => $session ? $session->cashierTerminal->financeAccount->id : null,
            ],
            'finance_accounts' => $this->commonDataService->getFinanceAccounts(),
        ]);
    }

    /**
     * Menyimpan pengajuan setoran baru.
     * Menggunakan redirect + flash message seperti OperationalCostController.
     */
    public function store(StoreRequest $request)
    {
        $this->authorize('create', CashierCashDrop::class);

        $data = $request->validated();
        $data['cashier_id'] = Auth::user()->id;

        // Ambil file gambar dari request jika ada
        $image = $request->hasFile('image') ? $request->file('image') : null;

        // Bersihkan image dari array data karena dipassing terpisah ke service
        unset($data['image']);

        $item = $this->service->create($data, $image);

        return redirect()
            ->route('admin.cashier-cash-drop.index')
            ->with("success", "Pengajuan setoran kas #$item->code berhasil dibuat.");
    }

    /**
     * Konfirmasi (Approve/Reject) oleh Supervisor.
     * Tetap menggunakan JsonResponseHelper karena biasanya dipanggil via aksi tabel/dialog tanpa reload halaman penuh.
     */
    public function confirm(ConfirmRequest $request)
    {
        $item = $this->service->find($request->id);

        // Pastikan user punya hak untuk mengkonfirmasi (bisa menggunakan policy 'confirm' atau 'update')
        $this->authorize('confirm', $item);

        if ($item->status != CashierCashDrop::Status_Pending) {
            throw new BusinessRuleViolationException('Setoran yang telah selesai tidak dapat diubah statusnya.');
        }

        $action_status_map = [
            'accept' => CashierCashDrop::Status_Approved,
            'reject' => CashierCashDrop::Status_Rejected,
        ];

        if (!isset($action_status_map[$request->action])) {
            throw new BusinessRuleViolationException('Aksi tidak valid.');
        }

        $this->service->confirm(
            $item,
            $action_status_map[$request->action],
            Auth::user()->id
        );

        $message = $request->action === 'accept' ? 'disetujui' : 'ditolak';

        return JsonResponseHelper::success($item, "Setoran kas #$item->code telah $message.");
    }

    public function delete($id)
    {
        $item = $this->service->find($id);

        $this->authorize('delete', $item);

        $this->service->delete($item);

        return JsonResponseHelper::success(
            $item,
            "Data setoran kas #$item->code telah dihapus."
        );
    }
}
