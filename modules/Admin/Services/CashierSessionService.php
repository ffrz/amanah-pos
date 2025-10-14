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

namespace Modules\Admin\Services;

use App\Exceptions\BusinessRuleViolationException;
use App\Models\CashierSession;
use App\Models\SalesOrder;
use App\Models\User;
use App\Models\UserActivityLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CashierSessionService
{
    public function __construct(
        protected UserActivityLogService $userActivityLogService,
        protected CashierTerminalService $cashierTerminalService,
        protected DocumentVersionService $documentVersionService,
    ) {}

    public function getActiveSession($userId = null)
    {
        return CashierSession::with(['cashierTerminal', 'cashierTerminal.financeAccount'])
            ->where('user_id', !$userId ? Auth::user()->id : $userId)
            ->where('is_closed', false)
            ->first();
    }

    public function find(int $id): CashierSession
    {
        return CashierSession::with(
            ['user', 'cashierTerminal', 'cashierTerminal.financeAccount', 'creator', 'updater']
        )->findOrFail($id);
    }

    public function findOrCreate($id): CashierSession
    {
        return $id ? CashierSession::find($id) : new CashierSession();
    }

    public function duplicate(int $id): CashierSession
    {
        return $this->find($id)->replicate();
    }

    public function getData(array $options): ?LengthAwarePaginator
    {
        $filter = $options['filter'];

        $q = CashierSession::with(['cashierTerminal', 'user']);

        if (!empty($filter['search'])) {
            $q->where(function ($q) use ($filter) {
                $q->orWhere('notes', 'like', '%' . $filter['search'] . '%');
            });
        }

        if (!empty($filter['status']) && $filter['status'] !== 'all') {
            $q->where('is_closed', $filter['status'] == 'closed' ? true : false);
        }

        if (!empty($filter['cashier_terminal_id']) && $filter['cashier_terminal_id'] !== 'all') {
            $q->where('cashier_terminal_id', $filter['cashier_terminal_id']);
        }

        $q->orderBy($options['order_by'], $options['order_type']);

        return $q->paginate($options['per_page'])->withQueryString();
    }

    public function open(array $data, User $user): mixed
    {
        $cashierTerminal = $this->cashierTerminalService->find($data['cashier_terminal_id']);

        return DB::transaction(function () use ($data, $user, $cashierTerminal) {
            $item = new CashierSession();
            $item->cashier_terminal_id = $data['cashier_terminal_id'];
            $item->user_id = $user->id;
            $item->opening_balance = $cashierTerminal->financeAccount->balance;
            $item->opening_notes = $data['opening_notes'];
            $item->is_closed = false;
            $item->opened_at = now();
            $item->save();

            $this->userActivityLogService->log(
                UserActivityLog::Category_CashierSession,
                UserActivityLog::Name_CashierSession_Open,
                "Sesi kasir $item->id telah dibuka.",
                [
                    'formatter' => 'cashier-session',
                    'new_data' => $item->toArray(),
                ]
            );

            $this->documentVersionService->createVersion($item);

            return $item;
        });
    }

    public function close(CashierSession $item, array $data): mixed
    {
        return DB::transaction(function () use ($item, $data) {
            $item->closing_notes = $data['closing_notes'];
            $item->is_closed = true;
            $item->closed_at = now();
            $item->save();

            $this->userActivityLogService->log(
                UserActivityLog::Category_CashierSession,
                UserActivityLog::Name_CashierSession_Close,
                "Sesi kasir $item->id telah ditutup.",
                [
                    'formatter' => 'cashier-session',
                    'new_data' => $item->toArray(),
                ]
            );

            $this->documentVersionService->createVersion($item);

            return $item;
        });
    }

    public function delete(CashierSession $item): mixed
    {
        if ($this->checkSessionInUsed($item)) {
            throw new BusinessRuleViolationException('Sesi kasir tidak dapat dihapus karena sudah ada transaksi.');
        }

        return DB::transaction(function () use ($item) {
            $item->delete();

            $this->userActivityLogService->log(
                UserActivityLog::Category_CashierSession,
                UserActivityLog::Name_CashierSession_Delete,
                "Sesi kasir $item->id telah dihapus.",
                [
                    'formatter' => 'cashier-session',
                    'data' => $item->toArray(),
                ]
            );

            $this->documentVersionService->createDeletedVersion($item);

            return $item;
        });
    }

    private function checkSessionInUsed(CashierSession $session): bool
    {
        return SalesOrder::where('cashier_session_id', $session->id)
            ->count() > 0;
    }
}
