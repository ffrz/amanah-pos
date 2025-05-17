<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FinanceAccount;
use App\Models\FinanceTransaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinanceAccountController extends Controller
{
    public function index()
    {
        return inertia('admin/finance-account/Index');
    }

    public function detail($id = 0)
    {
        return inertia('admin/finance-account/Detail', [
            'data' => FinanceAccount::findOrFail($id),
        ]);
    }

    public function data(Request $request)
    {
        $orderBy = $request->get('order_by', 'name');
        $orderType = $request->get('order_type', 'asc');
        $filter = $request->get('filter', []);

        $q = FinanceAccount::query();

        if (!empty($filter['search'])) {
            $q->where(function ($q) use ($filter) {
                $q->where('name', 'like', '%' . $filter['search'] . '%');
                $q->orWhere('bank', 'like', '%' . $filter['search'] . '%');
                $q->orWhere('holder', 'like', '%' . $filter['search'] . '%');
                $q->orWhere('number', 'like', '%' . $filter['search'] . '%');
                $q->orWhere('notes', 'like', '%' . $filter['search'] . '%');
            });
        }

        if (!empty($filter['status']) && ($filter['status'] == 'active' || $filter['status'] == 'inactive')) {
            $q->where('active', '=', $filter['status'] == 'active' ? true : false);
        }

        if (!empty($filter['type']) && $filter['type'] !== 'all') {
            $q->where('type', '=', $filter['type']);
        }

        $q->orderBy($orderBy, $orderType);

        $items = $q->paginate($request->get('per_page', 10))->withQueryString();

        return response()->json($items);
    }

    public function duplicate($id)
    {
        allowed_roles([User::Role_Admin]);
        $item = FinanceAccount::findOrFail($id);
        $item->id = null;
        $item->created_at = null;
        return inertia('admin/finance-account/Editor', [
            'data' => $item,
        ]);
    }

    public function editor($id = 0)
    {
        allowed_roles([User::Role_Admin]);
        $item = $id ? FinanceAccount::findOrFail($id) : new FinanceAccount(['active' => true]);
        return inertia('admin/finance-account/Editor', [
            'data' => $item,
        ]);
    }

    public function save(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:40|unique:finance_accounts,name' . ($request->id ? ',' . $request->id : ''),
            'type'     => 'required|in:' . implode(',', array_keys(FinanceAccount::Types)),
            'bank'     => 'nullable|string|max:40',
            'number'   => 'nullable|string|max:20',
            'holder'   => 'nullable|string|max:100',
            'balance'  => 'required|numeric',
            'active'   => 'required|boolean',
            'notes'    => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        $item = $request->id ? FinanceAccount::findOrFail($request->id) : new FinanceAccount();

        $isNew = !$request->id;
        $now = Carbon::now();
        $oldBalance = $item->balance ?? 0;

        $item->fill($validated);
        $item->save();

        $newBalance = $item->balance;
        $balanceDiff = $newBalance - $oldBalance;

        if ($isNew && $newBalance > 0) {
            FinanceTransaction::create([
                'account_id' => $item->id,
                'datetime' => $now,
                'type' => FinanceTransaction::Type_Adjustment,
                'amount'  => $newBalance,
                'notes' => 'Saldo awal akun',
            ]);
        } elseif (!$isNew && $balanceDiff !== 0) {
            FinanceTransaction::create([
                'account_id' => $item->id,
                'datetime' => $now,
                'type' => FinanceTransaction::Type_Adjustment, // pastikan enum/konstanta tersedia
                'amount' => $balanceDiff,
                'notes' => 'Penyesuaian saldo akun (dari ' . number_format($oldBalance) . ' ke ' . number_format($newBalance) . ')',
            ]);
        }

        DB::commit();

        return redirect(route('admin.finance-account.index'))->with('success', 'Informasi akun telah disimpan.');
    }

    public function delete($id)
    {
        allowed_roles([User::Role_Admin]);

        $item = FinanceAccount::findOrFail($id);
        $item->delete();

        return response()->json([
            'message' => __('messages.finance-account-deleted', ['name' => $item->name])
        ]);
    }
}
