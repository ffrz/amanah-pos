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

use App\Models\CashierTerminal;
use App\Models\FinanceAccount;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;

class CashierTerminalService
{
    /**
     * Mengambil daftar Terminal Kasir dengan pagination dan filter.
     */
    public function getData(array $filters, string $orderBy = 'name', string $orderType = 'asc', int $perPage = 10): LengthAwarePaginator
    {
        $q = CashierTerminal::with(['financeAccount']);

        if (!empty($filters['search'])) {
            $searchTerm = $filters['search'];
            $q->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('location', 'like', '%' . $searchTerm . '%')
                    ->orWhere('notes', 'like', '%' . $searchTerm . '%');
            });
        }

        if (isset($filters['status']) && ($filters['status'] === 'active' || $filters['status'] === 'inactive')) {
            $q->where('active', '=', $filters['status'] === 'active');
        }

        $q->orderBy($orderBy, $orderType);

        return $q->paginate($perPage);
    }

    /**
     * Mengambil Cashier Terminal berdasarkan ID dengan relasi detail.
     */
    public function getTerminal(int $id): CashierTerminal
    {
        return CashierTerminal::with(['financeAccount', 'creator', 'updater'])->findOrFail($id);
    }

    /**
     * Menyimpan (membuat atau memperbarui) Terminal Kasir.
     */
    public function save(array $data, ?int $id = null): CashierTerminal
    {
        $validated = Arr::except($data, ['finance_account_id']);

        if (Arr::get($data, 'finance_account_id') === 'new') {
            $financeAccount = $this->createAutoCashierAccount($validated['name']);
            $validated['finance_account_id'] = $financeAccount->id;
        } else {
            $validated['finance_account_id'] = Arr::get($data, 'finance_account_id');
        }

        $terminal = $id ? CashierTerminal::findOrFail($id) : new CashierTerminal();
        $terminal->fill($validated);
        $terminal->save();

        return $terminal;
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
        $item->delete();
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

    public function find($id): CashierTerminal
    {
        return CashierTerminal::with(['financeAccount'])->find($id);
    }
}
