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
use App\Models\CashierTerminal;
use App\Models\FinanceAccount;
use Illuminate\Http\Request;

class CashierTerminalController extends Controller
{
    public function index()
    {
        return inertia('cashier-terminal/Index');
    }

    public function detail($id = 0)
    {
        return inertia('cashier-terminal/Detail', [
            'data' => CashierTerminal::with(
                ['financeAccount', 'creator', 'updater']
            )->findOrFail($id),
        ]);
    }

    public function data(Request $request)
    {
        $orderBy = $request->get('order_by', 'name');
        $orderType = $request->get('order_type', 'asc');
        $filter = $request->get('filter', []);

        $q = CashierTerminal::with(['financeAccount']);

        if (!empty($filter['search'])) {
            $q->where(function ($q) use ($filter) {
                $q->where('name', 'like', '%' . $filter['search'] . '%');
                $q->orWhere('location', 'like', '%' . $filter['search'] . '%');
                $q->orWhere('notes', 'like', '%' . $filter['search'] . '%');
            });
        }

        if (!empty($filter['status']) && ($filter['status'] == 'active' || $filter['status'] == 'inactive')) {
            $q->where('active', '=', $filter['status'] == 'active' ? true : false);
        }

        $q->orderBy($orderBy, $orderType);

        $items = $q->paginate($request->get('per_page', 10))->withQueryString();

        return response()->json($items);
    }

    public function duplicate($id)
    {
        $item = CashierTerminal::findOrFail($id);
        $item->id = null;
        $item->created_at = null;
        return inertia('cashier-terminal/Editor', [
            'data' => $item,
        ]);
    }

    public function editor($id = 0)
    {
        $item = $id ? CashierTerminal::findOrFail($id) : new CashierTerminal(['active' => true]);

        $financeAccountsQuery = FinanceAccount::where('type', '=', FinanceAccount::Type_CashierCash);

        if ($id) {
            // Jika mengedit, sertakan akun kas yang sudah terhubung ke cash register ini
            $financeAccountsQuery->where(function ($query) use ($item) {
                $query->whereDoesntHave('cashierTerminal')
                    ->orWhere('id', '=', $item->finance_account_id);
            });
        } else {
            // Jika membuat baru, hanya sertakan akun kas yang belum terhubung
            $financeAccountsQuery->whereDoesntHave('cashierTerminal');
        }

        return inertia('cashier-terminal/Editor', [
            'data' => $item,
            'finance_accounts' => $financeAccountsQuery->orderBy('name', 'asc')->get()
        ]);
    }

    public function save(Request $request)
    {
        $rules = [
            'name'     => 'required|string|max:40|unique:cashier_terminals,name' . ($request->id ? ',' . $request->id : ''),
            'location' => 'nullable|max:255',
            'notes'    => 'nullable|max:255',
            'active'   => 'required|boolean',
        ];

        if ($request->post('finance_account_id') !== 'new') {
            $rules['finance_account_id'] = 'required|exists:finance_accounts,id';
        }

        $validated = $request->validate($rules);

        if ($request->post('finance_account_id') === 'new') {
            $accountName = 'Kas ' . $validated['name'];
            $baseName = $validated['name'];
            $suffix = 2;

            while (FinanceAccount::where('name', $accountName)->exists()) {
                $accountName = $baseName . ' ' . $suffix++;
            }

            $financeAccount = FinanceAccount::create([
                'name'    => $accountName,
                'type'    => FinanceAccount::Type_CashierCash,
                'active'  => true,
                'balance' => 0,
                'notes'   => 'Kas kasir dibuat otomatis.',
                'show_in_pos_payment'        => true, // wajib true
                'show_in_purchasing_payment' => true, // bisa beli dari kas toko
            ]);

            $validated['finance_account_id'] = $financeAccount->id;
        }

        $item = !$request->id ? new CashierTerminal() : CashierTerminal::findOrFail($request->id);
        $item->fill($validated);
        $item->save();

        return redirect(route('admin.cashier-terminal.index'))->with('success', "Terminal Kasir $item->name telah disimpan.");
    }

    public function delete($id)
    {
        $item = CashierTerminal::findOrFail($id);
        $item->delete();
        return JsonResponseHelper::success($item, "Terminal Kasir $item->name telah dihapus.");
    }
}
