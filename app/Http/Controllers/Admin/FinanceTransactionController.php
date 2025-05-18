<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FinanceAccount;
use App\Models\FinanceTransaction;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinanceTransactionController extends Controller
{

    public function index()
    {
        return inertia('admin/finance-transaction/Index', []);
    }

    public function data(Request $request)
    {
        $orderBy = $request->get('order_by', 'id');
        $orderType = $request->get('order_type', 'desc');
        $filter = $request->get('filter', []);

        $q = FinanceTransaction::with('account');

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

        $q->orderBy($orderBy, $orderType);

        $items = $q->paginate($request->get('per_page', 10))->withQueryString();

        return response()->json($items);
    }

    public function editor($id = 0)
    {
        allowed_roles([User::Role_Admin]);
        $item = $id ? FinanceTransaction::findOrFail($id) : new FinanceTransaction(['datetime' => Carbon::now()]);
        return inertia('admin/finance-transaction/Editor', [
            'data' => $item,
            'accounts' => FinanceAccount::where('active', '=', true)->orderBy('name', 'asc')->get()
        ]);
    }

    public function save(Request $request)
    {
        $route = 'admin.finance-transaction.index';

        $validated = $request->validate([
            'account_id'    => 'required|exists:finance_accounts,id',
            'to_account_id' => 'nullable|exists:finance_accounts,id|different:account_id',
            'datetime'      => 'required|date',
            'type'          => 'required|in:' . implode(',', array_keys(FinanceTransaction::Types)),
            'amount'        => 'required|numeric|min:0.01',
            'notes'         => 'nullable|string|max:255',
        ]);

        $validated['notes'] ?? '';

        if ($validated['type'] === FinanceTransaction::Type_Transfer && !empty($validated['to_account_id'])) {
            // Transaksi keluar dari akun asal
            $fromTransaction = new FinanceTransaction();
            $fromTransaction->fill([
                'account_id' => $validated['account_id'],
                'datetime'   => $validated['datetime'],
                'type'       => FinanceTransaction::Type_Transfer,
                'amount'     => -$validated['amount'], // keluar
                'notes'      => 'Transfer ke akun #' . $validated['to_account_id'] . '. ' . $validated['notes'],
            ]);
            $fromTransaction->save();

            // Update saldo akun asal
            $fromAccount = $fromTransaction->account;
            $fromAccount->balance += $fromTransaction->amount;
            $fromAccount->save();

            // Transaksi masuk ke akun tujuan
            $toTransaction = new FinanceTransaction();
            $toTransaction->fill([
                'account_id' => $validated['to_account_id'],
                'datetime'   => $validated['datetime'],
                'type'       => FinanceTransaction::Type_Transfer,
                'amount'     => $validated['amount'], // masuk
                'notes'      => 'Transfer dari akun #' . $validated['account_id'] . '. ' . $validated['notes'],
            ]);
            $toTransaction->save();

            // Update saldo akun tujuan
            $toAccount = $toTransaction->account;
            $toAccount->balance += $toTransaction->amount;
            $toAccount->save();

            // Link untuk keperluan delete
            $fromTransaction->ref_type = FinanceTransaction::RefType_FinanceTransaction;
            $fromTransaction->ref_id = $toTransaction->id;
            $fromTransaction->save();

            $toTransaction->ref_type = FinanceTransaction::RefType_FinanceTransaction;
            $toTransaction->ref_id = $fromTransaction->id;
            $toTransaction->save();

            DB::commit();

            return redirect(route($route))
                ->with('success', "Transfer antar akun berhasil disimpan.");
        } else {
            // Handle transaksi biasa (income / expense)
            $amount = $validated['amount'];
            if ($validated['type'] === FinanceTransaction::Type_Expense) {
                $amount = -$amount;
            }

            $transaction = new FinanceTransaction();
            $transaction->fill([
                'account_id'  => $validated['account_id'],
                'datetime'    => $validated['datetime'],
                'type'        => $validated['type'],
                'amount'      => $amount,
                'notes' => $validated['notes'],
            ]);
            $transaction->save();

            $account = $transaction->account;
            $account->balance += $amount;
            $account->save();

            DB::commit();

            return redirect(route($route))
                ->with('success', "Transaksi $transaction->id telah disimpan.");
        }
    }

    public function delete($id)
    {
        allowed_roles([User::Role_Admin]);

        $item = FinanceTransaction::findOrFail($id);
        if ($item->ref_type == FinanceTransaction::RefType_CustomerWalletTransaction) {
            // TODO: FIX apakah boleh hapus transaksi ??
            return response()->json([
                'message' => "Transaksi #$item->id tidak dapat dihapus karena berkaitan dengan transaksi wallet."
            ]);
        }

        DB::beginTransaction();
        $item->account->balance -= $item->amount;
        $item->account->save();

        if ($item->type === FinanceTransaction::Type_Transfer && $item->ref_type === FinanceTransaction::RefType_FinanceTransaction && $item->ref_id) {
            $pair = FinanceTransaction::find($item->ref_id);
            if ($pair) {
                // Kembalikan saldo akun tujuan
                $pair->account->balance -= $pair->amount;
                $pair->account->save();

                // Hapus pasangan
                $pair->delete();
            }
        }

        $item->delete();

        DB::commit();

        return response()->json([
            'message' => "Transaksi #$item->id telah dihapus."
        ]);
    }
}
