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
use App\Models\SupplierLedger;
use Modules\Admin\Services\CommonDataService;
use Modules\Admin\Services\SupplierLedgerService;
use Illuminate\Http\Request;
use Modules\Admin\Http\Requests\SupplierLedger\GetDataRequest;
use Modules\Admin\Http\Requests\SupplierLedger\SaveRequest;
use Modules\Admin\Http\Requests\SupplierLedger\AdjustmentRequest;

class SupplierLedgerController extends Controller
{
    public function __construct(
        protected CommonDataService $commonDataService,
        protected SupplierLedgerService $service
    ) {}

    public function index()
    {
        $this->authorize('viewAny', SupplierLedger::class);

        return inertia('supplier-ledger/Index', []);
    }

    public function data(GetDataRequest $request)
    {
        $this->authorize('viewAny', SupplierLedger::class);

        $items = $this->service->getData($request->validated())->withQueryString();

        return JsonResponseHelper::success($items);
    }

    /**
     * Form Editor untuk Manual Entry (Utang Lama, Koreksi, dll).
     */
    public function editor()
    {
        $this->authorize('create', SupplierLedger::class);

        return inertia('supplier-ledger/Editor', [
            'data' => [
                'id' => null,
                'datetime' => now(),
                'type' => SupplierLedger::Type_OpeningBalance,
                'amount' => 0,
                'notes' => '',
            ],
            'suppliers' => $this->commonDataService->getSuppliers(),
            'finance_accounts' => $this->commonDataService->getFinanceAccounts(),
            'types' => SupplierLedger::Types,
        ]);
    }

    public function save(SaveRequest $request)
    {
        $this->authorize('create', SupplierLedger::class);

        // Service akan otomatis handle tanda positif/negatif berdasarkan tipe
        $item = $this->service->save($request->validated(), $request->file('image'));

        return redirect(route('admin.supplier-ledger.index'))
            ->with('success', "Transaksi ledger {$item->code} berhasil dicatat.");
    }

    /**
     * Halaman & Proses Penyesuaian Saldo (Opname Utang).
     */
    public function adjustment(AdjustmentRequest $request)
    {
        if ($request->getMethod() === 'GET') {
            $this->authorize('create', SupplierLedger::class);

            return inertia('supplier-ledger/Adjustment', [
                'suppliers' => $this->commonDataService->getSuppliers(),
            ]);
        }

        $this->authorize('create', SupplierLedger::class);

        $item = $this->service->adjustBalance($request->validated());

        return redirect(route('admin.supplier-ledger.index'))
            ->with('success', "Saldo utang pemasok {$item->supplier->name} telah disesuaikan.");
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

    public function detail($id)
    {
        $item = $this->service->find($id);

        $this->authorize('view', $item);

        return inertia('supplier-ledger/Detail', [
            'data' => $item
        ]);
    }
}
