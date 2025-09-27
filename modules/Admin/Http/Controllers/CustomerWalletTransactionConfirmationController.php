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

use App\Helpers\ImageUploaderHelper;
use App\Helpers\JsonResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\CustomerWalletTransaction;
use App\Models\CustomerWalletTransactionConfirmation;
use App\Models\FinanceTransaction;
use App\Services\CustomerWalletTransactionService;
use App\Services\FinanceTransactionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerWalletTransactionConfirmationController extends Controller
{
    protected $financeTransactionService;
    protected $customerWalletTransactionService;

    public function __construct(
        FinanceTransactionService $financeTransactionService,
        CustomerWalletTransactionService $customerWalletTransactionService
    ) {
        $this->financeTransactionService = $financeTransactionService;
        $this->customerWalletTransactionService = $customerWalletTransactionService;
    }

    public function index()
    {
        return inertia('customer-wallet-transaction-confirmation/Index', []);
    }

    public function detail($id)
    {
        $item = CustomerWalletTransactionConfirmation::with(['financeAccount', 'customer', 'updater'])
            ->findOrFail($id);

        return inertia('customer-wallet-transaction-confirmation/Detail', [
            'data' => $item
        ]);
    }

    public function data(Request $request)
    {
        $orderBy = $request->get('order_by', 'datetime');
        $orderType = $request->get('order_type', 'desc');
        $filter = $request->get('filter', []);

        $q = CustomerWalletTransactionConfirmation::with(['customer', 'financeAccount']);

        if (!empty($filter['search'])) {
            $q->where(function ($q) use ($filter) {
                $q->orWhere('notes', 'like', '%' . $filter['search'] . '%');
            });
        }

        if (!empty($filter['year']) && $filter['year'] !== 'all') {
            $q->whereYear('datetime', $filter['year']);

            if (!empty($filter['month']) && $filter['month'] !== 'all') {
                $q->whereMonth('datetime', $filter['month']);
            }
        }

        if (!empty($filter['customer_id']) && $filter['customer_id'] != 'all') {
            $q->where('customer_id', $filter['customer_id']);
        }

        if (!empty($filter['finance_account_id']) && $filter['finance_account_id'] != 'all') {
            $q->where('finance_account_id', '=', $filter['finance_account_id']);
        }

        if (!empty($filter['status']) && $filter['status'] != 'all') {
            $q->where('status', '=', $filter['status']);
        }

        $q->orderBy($orderBy, $orderType);

        $items = $q->paginate($request->get('per_page', 10))->withQueryString();

        return response()->json($items);
    }

    public function save(Request $request)
    {
        $item = CustomerWalletTransactionConfirmation::findOrFail($request->id);

        if ($item->status != CustomerWalletTransactionConfirmation::Status_Pending) {
            abort(400, 'Transaksi yang telah selesai tidak dapat diubah.');
        }

        if ($request->action == 'reject') {
            $item->status = CustomerWalletTransactionConfirmation::Status_Rejected;
            $item->save();
            return redirect(route('admin.customer-wallet-transaction-confirmation.index'))
                ->with('success', "Konfirmasi topup $item->formatted_id telah ditolak.");
        }

        if ($request->action != 'accept') {
            abort(400, 'Aksi tidak dikenali!');
            return;
        }

        DB::beginTransaction();
        $item->status = CustomerWalletTransactionConfirmation::Status_Approved;
        $item->save();

        // TODO: pindahkan ke service

        $walletTransaction = $this->customerWalletTransactionService->handleTransaction([
            'customer_id' => $item->customer_id,
            'finance_account_id' => $item->finance_account_id,
            'datetime' => Carbon::now(),
            'type' => CustomerWalletTransaction::Type_Deposit,
            'ref_type' => CustomerWalletTransaction::RefType_CustomerWalletTransactionConfirmation,
            'ref_id' => $item->id,
            'amount' => $item->amount,
            'notes' => 'Konfirmasi topup wallet otomatis #' . $item->formatted_id,
        ]);

        $this->financeTransactionService->handleTransaction([
            'datetime' => Carbon::now(),
            'account_id' => $item->finance_account_id,
            'amount' => $item->amount,
            'type' => FinanceTransaction::Type_Income,
            'notes' => 'Transaksi topup wallet customer ' . $item->customer->code . ' Ref: #' . $walletTransaction->formatted_id,
            'ref_type' => FinanceTransaction::RefType_CustomerWalletTransaction,
            'ref_id' => $item->id,
        ]);

        DB::commit();

        return JsonResponseHelper::success($item, "Konfirmasi topup #$item->formatted_id telah diterima.");
    }

    public function delete($id)
    {
        $item = CustomerWalletTransactionConfirmation::findOrFail($id);

        DB::beginTransaction();
        try {
            // Step 1: kembalikan saldo wallet dan hapus transaksi wallet jika sudah dicatat
            $this->customerWalletTransactionService->reverseTransaction(
                $item->id,
                CustomerWalletTransaction::RefType_CustomerWalletTransactionConfirmation
            );

            // Step 2: kembalikan saldo akun jika sudah dicatat dan hapus transaksinya
            if ($item->status === CustomerWalletTransactionConfirmation::Status_Approved) {
                $this->financeTransactionService->reverseTransaction($item->id, FinanceTransaction::RefType_CustomerWalletTransaction);
            }

            // Step 3: Delete the confirmation record
            $item->delete();

            // Step 4: Delete the image
            ImageUploaderHelper::deleteImage($item->image_path);

            DB::commit();

            return JsonResponseHelper::success($item, "Konfirmasi topup #$item->formatted_id telah dihapus.");
        } catch (\Throwable $ex) {
            DB::rollBack();

            // Log the exception for debugging purposes
            // logger()->error("Error deleting top-up confirmation: " . $ex->getMessage());

            return JsonResponseHelper::error("Terdapat kesalahan saat menghapus rekaman.", 500, $ex);
        }
    }
}
