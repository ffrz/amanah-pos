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
use App\Models\CashierSession;
use App\Models\CashRegister;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CashierSessionController extends Controller
{
    public function index()
    {
        return inertia('cashier-session/Index');
    }

    public function detail($id = 0)
    {
        return inertia('cashier-session/Detail', [
            'data' => CashierSession::with(
                ['cashRegister', 'user', 'creator', 'updater']
            )->findOrFail($id),
        ]);
    }

    public function data(Request $request)
    {
        $orderBy = $request->get('order_by', 'id');
        $orderType = $request->get('order_type', 'desc');
        $filter = $request->get('filter', []);

        $q = CashierSession::with(['cashRegister', 'user']);

        if (!empty($filter['search'])) {
            $q->where(function ($q) use ($filter) {
                $q->orWhere('notes', 'like', '%' . $filter['search'] . '%');
            });
        }

        if (!empty($filter['status']) && $filter['status'] !== 'all') {
            $q->where('is_closed', '=', $filter['status'] == 'closed' ? true : false);
        }

        $q->orderBy($orderBy, $orderType);

        $items = $q->paginate($request->get('per_page', 10))->withQueryString();

        return JsonResponseHelper::success($items);
    }

    public function start(Request $request)
    {
        // Cek apakah user sudah punya sesi aktif
        $activeSession = CashierSession::where('user_id', Auth::user()->id)
            ->where('is_closed', false)
            ->first();

        // Redirect jika sudah ada sesi aktif
        if ($activeSession) {
            return redirect(route('admin.cashier-session.detail', $activeSession->id))
                ->with('warning', 'Anda sudah memiliki sesi kasir aktif.');
        }

        if ($request->method() == Request::METHOD_POST) {
            $validated = $request->validate([
                'cash_register_id' => [
                    'required',
                    'exists:cash_registers,id',
                    // Pastikan cash register tidak sedang memiliki sesi aktif
                    Rule::unique('cashier_sessions', 'cash_register_id')->where(function ($query) {
                        return $query->where('is_closed', false);
                    }),
                ],
                'opening_notes' => 'nullable|string|max:200',
            ]);

            $cashRegister = CashRegister::with(['financeAccount'])
                ->findOrFail($validated['cash_register_id']);

            $item = new CashierSession();
            $item->cash_register_id = $validated['cash_register_id'];
            $item->user_id = Auth::user()->id;
            $item->opening_balance = $cashRegister->financeAccount->balance;
            $item->opening_notes = $validated['opening_notes'] ?? '';
            $item->is_closed = false;
            $item->started_at = now();
            $item->save();

            return redirect(route('admin.cashier-session.detail', $item->id))
                ->with('success', "Sesi kasir {$cashRegister->name} telah dimulai.");
        }

        // Ambil cash register yang tidak sedang memiliki sesi aktif
        $availableCashRegisters = CashRegister::with(['financeAccount'])
            ->where('active', '=', true)
            ->whereDoesntHave('activeSession')
            ->orderBy('name')
            ->get();

        return inertia('cashier-session/Start', [
            'data' => new CashierSession(),
            'cash_registers' => $availableCashRegisters,
        ]);
    }

    public function stop(Request $request, $id)
    {
        $item = CashierSession::with(['user', 'cashRegister', 'cashRegister.financeAccount'])->findOrFail($id);
        $item->closing_balance = $item->cashRegister->financeAccount->balance;

        if ($request->method() == Request::METHOD_POST) {
            $validated = $request->validate([
                'closing_notes' => 'nullable|string|max:200',
            ]);

            $item->closing_notes = $validated['opening_notes'] ?? '';
            $item->is_closed = true;
            $item->closed_at = now();
            $item->save();

            return redirect(route('admin.cashier-session.detail', $item->id))
                ->with('success', "Sesi kasir {$item->cash_register->name} telah ditutup.");
        }

        return inertia('cashier-session/Stop', [
            'data' => $item,
        ]);
    }

    public function delete($id)
    {
        $item = CashierSession::findOrFail($id);
        $item->delete();
        return JsonResponseHelper::success($item, "Sesi kasir $item->name telah dihapus.");
    }
}
