<?php

/**
 * Proprietary Software / Perangkat Lunak Proprietary
 * Copyright (c) 2025 Fahmi Fauzi Rahman. All rights reserved.
 *  * EN: Unauthorized use, copying, modification, or distribution is prohibited.
 * ID: Penggunaan, penyalinan, modifikasi, atau distribusi tanpa izin dilarang.
 *  * See the LICENSE file in the project root for full license information.
 * Lihat file LICENSE di root proyek untuk informasi lisensi lengkap.
 *  * GitHub: https://github.com/ffrz
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
        $balance = $this->financeAccountService->getTotalActiveAccountBalance();

        return inertia('finance-account/Index', [
            'totalBalance' => $balance,
        ]);
    }

    public function detail($id = 0)
    {
        $item = $this->financeAccountService->find($id);
        return inertia('finance-account/Detail', [
            'data' => $item,
        ]);
    }

    /**
     * Menggunakan Form Request untuk validasi dan penyiapan data query.
     */
    public function data(GetDataRequest $request)
    {
        // Hanya memanggil Service untuk menjalankan query paginasi
        $items = $this->financeAccountService->getData(
            $request->validated()
        );

        return JsonResponseHelper::success($items);
    }

    public function duplicate($id)
    {
        $item = $this->financeAccountService->duplicate($id);
        return inertia('finance-account/Editor', [
            'data' => $item,
        ]);
    }

    public function editor($id = 0)
    {
        $item = $this->financeAccountService->findOrCreate($id);
        return inertia('finance-account/Editor', [
            'data' => $item,
        ]);
    }

    /**
     * Menggunakan Form Request untuk validasi input POST/PUT.
     */
    public function save(SaveRequest $request)
    {
        try {
            $item = $this->financeAccountService->findOrCreate($request->id);
            $item = $this->financeAccountService->save(
                $item,
                $request->validated()
            );

            return redirect(route('admin.finance-account.index'))
                ->with('success', "Akun $item->name telah disimpan.");
        } catch (\Throwable $e) {
            Log::error("Gagal menyimpan akun ID: $request->id", ['exception' => $e]);
        }
        return redirect()->back()->withInput()
            ->with('error', $e->getMessage());
    }

    public function delete($id)
    {
        try {
            $item = $this->financeAccountService->findOrCreate($id);

            $item = $this->financeAccountService->delete($item);

            return JsonResponseHelper::success(
                $item,
                "Akun kas $item->name telah dihapus."
            );
        } catch (\Exception $e) {
            Log::error("Gagal menghapus akun ID: $id", ['execption' => $e]);
            return JsonResponseHelper::error("Gagal menghapus akun ", 403);
        }
    }
}
