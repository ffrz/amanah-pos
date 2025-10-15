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

use App\Exceptions\BusinessRuleViolationException;
use App\Helpers\ImageUploaderHelper;
use App\Helpers\JsonResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\CustomerWalletTransaction;
use App\Models\CustomerWalletTransactionConfirmation;
use App\Models\FinanceTransaction;
use Modules\Admin\Services\CustomerWalletTransactionService;
use Modules\Admin\Services\FinanceTransactionService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Admin\Http\Requests\CustomerWalletTransactionConfirmation\GetDataRequest;
use Modules\Admin\Http\Requests\CustomerWalletTransactionConfirmation\SaveRequest;
use Modules\Admin\Services\CustomerWalletTransactionConfirmationService;

class CustomerWalletTransactionConfirmationController extends Controller
{
    public function __construct(
        protected FinanceTransactionService $financeTransactionService,
        protected CustomerWalletTransactionService $customerWalletTransactionService,
        protected CustomerWalletTransactionConfirmationService $service,
    ) {}

    public function index()
    {
        return inertia('customer-wallet-transaction-confirmation/Index', []);
    }

    public function detail($id)
    {
        $item = $this->service->find($id);

        return inertia('customer-wallet-transaction-confirmation/Detail', [
            'data' => $item
        ]);
    }

    public function data(GetDataRequest $request)
    {
        $items = $this->service->getData($request->validated())->withQueryString();

        return JsonResponseHelper::success($items);
    }

    public function save(SaveRequest $request)
    {
        $item = $this->service->find($request->id);

        if ($item->status != CustomerWalletTransactionConfirmation::Status_Pending) {
            throw new BusinessRuleViolationException('Transaksi yang telah selesai tidak dapat diubah.');
        }

        $action_status_map = [
            'accept' => CustomerWalletTransactionConfirmation::Status_Approved,
            'reject' => CustomerWalletTransactionConfirmation::Status_Rejected,
        ];

        $this->service->save($item, $action_status_map[$request->action]);

        return JsonResponseHelper::success($item, "Konfirmasi topup #$item->code telah diterima.");
    }

    public function delete($id)
    {
        $item = $this->service->find($id);

        $item = $this->service->delete($item);

        return JsonResponseHelper::success($item, "Konfirmasi topup #$item->code telah dihapus.");
    }
}
