<?php

/**
 * Proprietary Software / Perangkat Lunak Proprietary
 * Copyright (c) 2025 Fahmi Fauzi Rahman. All rights reserved.
 * * EN: Unauthorized use, copying, modification, or distribution is prohibited.
 * ID: Penggunaan, penyalinan, modifikasi, atau distribusi tanpa izin dilarang.
 * * Class ini menangani semua logika bisnis terkait CashierTerminal (Terminal Kasir).
 * Ini adalah Service Layer utama.
 */

namespace Modules\Admin\Services;

use App\Exceptions\ModelNotModifiedException;
use App\Models\CashierTerminal;
use App\Models\FinanceAccount;
use App\Models\UserActivityLog;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class CashierTerminalService
{
    public function __construct(
        protected DocumentVersionService $documentVersionService,
        protected UserActivityLogService $userActivityLogService
    ) {}

    /**
     * Mengambil daftar Terminal Kasir dengan pagination dan filter.
     */
    public function getData(array $options): LengthAwarePaginator
    {
        $filter = $options['filter'];

        $q = CashierTerminal::with(['financeAccount']);

        if (!empty($filter['search'])) {
            $searchTerm = $filter['search'];
            $q->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('location', 'like', '%' . $searchTerm . '%')
                    ->orWhere('notes', 'like', '%' . $searchTerm . '%');
            });
        }

        if (isset($filter['status']) && ($filter['status'] === 'active' || $filter['status'] === 'inactive')) {
            $q->where('active', '=', $filter['status'] === 'active');
        }

        $q->orderBy($options['order_by'], $options['order_type']);

        return $q->paginate($options['per_page']);
    }

    /**
     * Mengambil Cashier Terminal berdasarkan ID dengan relasi detail.
     */
    public function find(int $id): CashierTerminal
    {
        return CashierTerminal::with(['financeAccount', 'creator', 'updater'])->findOrFail($id);
    }

    /**
     * Menyimpan (membuat atau memperbarui) Terminal Kasir.
     */
    public function save(CashierTerminal $item, array $data): CashierTerminal
    {
        $isNew = empty($data['id']);

        if ($data['finance_account_id'] === 'new') {
            $financeAccount = $this->createAutoCashierAccount($data['name']);
            $data['finance_account_id'] = $financeAccount->id;
        }

        $oldData = $item->toArray();
        $item->fill($data);

        if (empty($item->getDirty())) {
            throw new ModelNotModifiedException();
        }

        return DB::transaction(function () use ($isNew, $oldData, $item) {
            $item->save();

            $this->documentVersionService->createVersion($item);

            if ($isNew) {
                $this->userActivityLogService->log(
                    UserActivityLog::Category_CashierTerminal,
                    UserActivityLog::Name_CashierTerminal_Create,
                    "Terminal kasir $item->name telah dibuat.",
                    [
                        'formatter' => 'cashier-terminal',
                        'data' => $item->toArray(),
                    ],
                );
            } else {
                $this->userActivityLogService->log(
                    UserActivityLog::Category_CashierTerminal,
                    UserActivityLog::Name_CashierTerminal_Delete,
                    "Terminal kasir $item->name telah diperbarui.",
                    [
                        'formatter' => 'cashier-terminal',
                        'new_data' => $item->toArray(),
                        'old_data' => $oldData,
                    ],
                );
            }

            return $item;
        });
    }

    /**
     * Membuat Akun Kasir (FinanceAccount) baru otomatis.
     */
    public function createAutoCashierAccount(string $terminalName): FinanceAccount
    {
        $accountName = 'Kas ' . $terminalName;
        $baseName = $terminalName; // Gunakan terminalName sebagai base untuk penamaan
        $suffix = 2;

        while (FinanceAccount::where('name', $accountName)->exists()) {
            $accountName = 'Kas ' . $baseName . ' ' . $suffix++;
        }

        return FinanceAccount::create([
            'name'    => $accountName,
            'type'    => FinanceAccount::Type_CashierCash,
            'active'  => true,
            'balance' => 0,
            'notes'   => 'Kas kasir dibuat otomatis dari terminal ' . $terminalName . '.',
            'show_in_pos_payment' => true,
            'show_in_purchasing_payment' => true,
        ]);
    }

    /**
     * Menghapus Terminal Kasir.
     */
    public function delete($item): CashierTerminal
    {
        return DB::transaction(function () use ($item) {
            $item = $item->delete();

            $this->userActivityLogService->log(
                UserActivityLog::Category_CashierTerminal,
                UserActivityLog::Name_CashierTerminal_Delete,
                "Terminal kasir $item->name telah dihapus.",
                $item->getAttributes(),
            );

            $this->documentVersionService->createDeletedVersion($item);
        });

        return $item;
    }

    /**
     * Mengambil daftar FinanceAccount yang tersedia untuk Cashier Terminal.
     */
    public function getAvailableFinanceAccounts(?int $terminalId = null): Collection
    {
        $query = FinanceAccount::where('type', '=', FinanceAccount::Type_CashierCash);

        if ($terminalId) {
            $item = CashierTerminal::findOrFail($terminalId);
            $query->where(function ($query) use ($item) {
                $query->whereDoesntHave('cashierTerminal')
                    ->orWhere('id', '=', $item->finance_account_id);
            });
        } else {
            $query->whereDoesntHave('cashierTerminal');
        }

        return $query->orderBy('name', 'asc')->get();
    }

    public function getAvailableCashierTerminals(): Collection
    {
        return CashierTerminal::with(['financeAccount'])
            ->where('active', '=', true)
            ->whereDoesntHave('activeSession')
            ->orderBy('name')
            ->get();
    }

    public function findOrCreate($id): CashierTerminal
    {
        return $id ? $this->find($id) : new CashierTerminal();
    }

    public function duplicate(int $id): CashierTerminal
    {
        return $this->find($id)->replicate();
    }
}
