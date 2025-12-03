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
use App\Models\CustomerLedger;
use Modules\Admin\Services\CommonDataService;
use Modules\Admin\Services\CustomerLedgerService;
use Illuminate\Http\Request;
// Asumsi Request Class sudah dibuat terpisah atau bisa pakai inline validate
use Modules\Admin\Http\Requests\CustomerLedger\GetDataRequest;
use Modules\Admin\Http\Requests\CustomerLedger\SaveRequest;
use Modules\Admin\Http\Requests\CustomerLedger\AdjustmentRequest;

class CustomerLedgerController extends Controller
{
    public function __construct(
        protected CommonDataService $commonDataService,
        protected CustomerLedgerService $service
    ) {}

    public function index()
    {
        $this->authorize('viewAny', CustomerLedger::class);

        return inertia('customer-ledger/Index', []);
    }

    public function data(GetDataRequest $request)
    {
        $this->authorize('viewAny', CustomerLedger::class);

        $items = $this->service->getData($request->validated())->withQueryString();

        return JsonResponseHelper::success($items);
    }

    /**
     * Form untuk mencatat transaksi manual (Utang lama, Bonus, dll).
     * NOTE: Kita jarang mengedit ledger yang sudah terposting (id selalu 0/baru).
     */
    public function editor()
    {
        $this->authorize('create', CustomerLedger::class);

        return inertia('customer-ledger/Editor', [
            // Kirim data kosong sebagai inisialisasi form
            'data' => [
                'id' => null,
                'datetime' => now(),
                'type' => CustomerLedger::Type_OpeningBalance, // Default
                'amount' => 0,
                'notes' => '',
            ],
            // Data pendukung dropdown
            'customers' => $this->commonDataService->getCustomers(),
            'finance_accounts' => $this->commonDataService->getFinanceAccounts(),
            // Kirim Type Enum agar Frontend bisa render dropdown jenis transaksi
            'types' => CustomerLedger::Types,
        ]);
    }

    /**
     * Menyimpan Transaksi Manual.
     * Menggunakan method save() di service.
     */
    public function save(SaveRequest $request)
    {
        $this->authorize('create', CustomerLedger::class);

        $item = $this->service->save($request->validated(), $request->file('image'));

        return redirect(route('admin.customer-ledger.index'))
            ->with('success', "Transaksi ledger {$item->code} berhasil dicatat.");
    }

    /**
     * Fitur Opname/Penyesuaian Saldo Akhir.
     * Menggunakan method adjustBalance() di service.
     */
    public function adjustment(AdjustmentRequest $request)
    {
        // Jika GET, tampilkan Form Adjustment
        if ($request->getMethod() === 'GET') {
            $this->authorize('create', CustomerLedger::class);

            return inertia('customer-ledger/Adjustment', [
                'customers' => $this->commonDataService->getCustomers(),
            ]);
        }

        // Jika POST, Proses Adjustment
        $this->authorize('create', CustomerLedger::class);

        $item = $this->service->adjustBalance($request->validated());

        return redirect(route('admin.customer-ledger.index'))
            ->with('success', "Saldo piutang pelanggan {$item->customer->name} telah disesuaikan.");
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

        return inertia('customer-ledger/Detail', [
            'data' => $item
        ]);
    }
}
