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
use App\Models\CashRegister;
use App\Models\FinanceAccount;
use Illuminate\Http\Request;

class CashRegisterController extends Controller
{
    public function index()
    {
        return inertia('cash-register/Index');
    }

    public function detail($id = 0)
    {
        return inertia('cash-register/Detail', [
            'data' => CashRegister::with(
                ['financeAccount', 'creator', 'updater']
            )->findOrFail($id),
        ]);
    }

    public function data(Request $request)
    {
        $orderBy = $request->get('order_by', 'name');
        $orderType = $request->get('order_type', 'asc');
        $filter = $request->get('filter', []);

        $q = CashRegister::with(['financeAccount']);

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
        $item = CashRegister::findOrFail($id);
        $item->id = null;
        $item->created_at = null;
        return inertia('cash-register/Editor', [
            'data' => $item,
        ]);
    }

    public function editor($id = 0)
    {
        $item = $id ? CashRegister::findOrFail($id) : new CashRegister(['active' => true]);

        $financeAccountsQuery = FinanceAccount::where('type', '=', FinanceAccount::Type_CashierCash);

        if ($id) {
            // Jika mengedit, sertakan akun kas yang sudah terhubung ke cash register ini
            $financeAccountsQuery->where(function ($query) use ($item) {
                $query->whereDoesntHave('cashRegister')
                    ->orWhere('id', '=', $item->finance_account_id);
            });
        } else {
            // Jika membuat baru, hanya sertakan akun kas yang belum terhubung
            $financeAccountsQuery->whereDoesntHave('cashRegister');
        }

        return inertia('cash-register/Editor', [
            'data' => $item,
            'finance_accounts' => $financeAccountsQuery->orderBy('name', 'asc')->get()
        ]);
    }

    public function save(Request $request)
    {
        $rules = [
            'name'     => 'required|string|max:40|unique:cash_registers,name' . ($request->id ? ',' . $request->id : ''),
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

        $item = !$request->id ? new CashRegister() : CashRegister::findOrFail($request->id);
        $item->fill($validated);
        $item->save();

        return redirect(route('admin.cash-register.index'))->with('success', "Cash Register $item->name telah disimpan.");
    }

    public function delete($id)
    {
        $item = CashRegister::findOrFail($id);
        $item->delete();
        return JsonResponseHelper::success($item, "Cash Register $item->name telah dihapus.");
    }
}
