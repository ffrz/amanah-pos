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
use App\Models\CustomerWalletTransaction;
use App\Models\CustomerWalletTransactionConfirmation;
use App\Models\FinanceAccount;
use App\Models\FinanceTransaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerWalletTransactionConfirmationController extends Controller
{

    public function index()
    {
        return inertia('customer-wallet-transaction-confirmation/Index', []);
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
            abort(400, 'Aksi tidak dapat dilakukan, status sudah tidak pending!');
        }

        if ($request->action == 'reject') {
            $item->status = CustomerWalletTransactionConfirmation::Status_Rejected;
            $item->save();
            return redirect(route('admin.customer-wallet-transaction-confirmation.index'))
                ->with('success', "Konfirmasi topup $item->formatted_id telah ditolak.");
        }

        if ($request->action != 'accept') {
            abort(400, 'Aksi tidak dikenali!');
        }

        DB::beginTransaction();
        $item->status = CustomerWalletTransactionConfirmation::Status_Approved;
        $item->save();

        $walletTransaction  = new CustomerWalletTransaction([
            'customer_id' => $item->customer_id,
            'finance_account_id' => $item->finance_account_id,
            'datetime' => Carbon::now(),
            'type' => CustomerWalletTransaction::Type_Deposit,
            'amount' => $item->amount,
            'notes' => 'Konfirmasi topup wallet otomatis #' . $item->formatted_id,
        ]);
        $walletTransaction->save();

        $customer = $item->customer;
        $customer->balance += $item->amount;
        $customer->save();

        $account = FinanceAccount::findOrFail($item->finance_account_id);
        $account->balance += $item->amount;
        $account->save();

        FinanceTransaction::create([
            'datetime' => Carbon::now(),
            'account_id' => $item->finance_account_id,
            'amount' => $item->amount,
            'type' => FinanceTransaction::Type_Income,
            'notes' => 'Transaksi topup wallet customer #' . $customer->id,
            'ref_type' => FinanceTransaction::RefType_CustomerWalletTransaction,
            'ref_id' => $item->id,
        ]);

        DB::commit();

        return JsonResponseHelper::success($item, "Konfirmasi topup #$item->id telah diterima.");
    }

    public function delete($id)
    {
        allowed_roles([User::Role_Admin]);

        DB::beginTransaction();
        $item = CustomerWalletTransactionConfirmation::findOrFail($id);
        $item->delete();
        DB::commit();

        return JsonResponseHelper::success($item, "Konfirmasi topup #$item->id telah dihapus.");
    }
}
