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
use App\Models\CashierTerminal;
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
                ['cashierTerminal', 'user', 'creator', 'updater']
            )->findOrFail($id),
        ]);
    }

    public function data(Request $request)
    {
        $orderBy = $request->get('order_by', 'id');
        $orderType = $request->get('order_type', 'desc');
        $filter = $request->get('filter', []);

        $q = CashierSession::with(['cashierTerminal', 'user']);

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

    public function open(Request $request)
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
                'cashier_terminal_id' => [
                    'required',
                    'exists:cashier_terminals,id',
                    // Pastikan cash register tidak sedang memiliki sesi aktif
                    Rule::unique('cashier_sessions', 'cashier_terminal_id')->where(function ($query) {
                        return $query->where('is_closed', false);
                    }),
                ],
                'opening_notes' => 'nullable|string|max:200',
            ]);

            $cashierTerminal = CashierTerminal::with(['financeAccount'])
                ->findOrFail($validated['cashier_terminal_id']);

            $item = new CashierSession();
            $item->cashier_terminal_id = $validated['cashier_terminal_id'];
            $item->user_id = Auth::user()->id;
            $item->opening_balance = $cashierTerminal->financeAccount->balance;
            $item->opening_notes = $validated['opening_notes'] ?? '';
            $item->is_closed = false;
            $item->opened_at = now();
            $item->save();

            return redirect(route('admin.cashier-session.detail', $item->id))
                ->with('success', "Sesi kasir {$cashierTerminal->name} telah dimulai.");
        }

        // Ambil cash register yang tidak sedang memiliki sesi aktif
        $availableCashierTerminals = CashierTerminal::with(['financeAccount'])
            ->where('active', '=', true)
            ->whereDoesntHave('activeSession')
            ->orderBy('name')
            ->get();

        return inertia('cashier-session/Open', [
            'data' => new CashierSession(),
            'cashier_terminals' => $availableCashierTerminals,
        ]);
    }

    public function close(Request $request, $id)
    {
        $item = CashierSession::with(['user', 'cashierTerminal', 'cashierTerminal.financeAccount'])->findOrFail($id);
        $item->closing_balance = $item->cashierTerminal->financeAccount->balance;

        if ($item->is_closed) {
            return redirect()->back()->with('warning', 'Sesi yang telah selesai tidak bisa ditutup!');
        }

        if ($request->method() == Request::METHOD_POST) {
            $validated = $request->validate([
                'closing_notes' => 'nullable|string|max:200',
            ]);

            $item->closing_notes = $validated['opening_notes'] ?? '';
            $item->is_closed = true;
            $item->closed_at = now();
            $item->save();

            return redirect(route('admin.cashier-session.detail', $item->id))
                ->with('success', "Sesi kasir {$item->id} telah ditutup.");
        }

        return inertia('cashier-session/Close', [
            'data' => $item,
        ]);
    }

    public function delete($id)
    {
        $item = CashierSession::findOrFail($id);
        try {
            $item->delete();
            return JsonResponseHelper::success($item, "Sesi kasir #$item->id telah dihapus.");
        } catch (\Throwable $ex) {
            return JsonResponseHelper::error("Sesi kasir #$item->id tidak dapat dihapus.", 500, $ex);
        }
    }
}
