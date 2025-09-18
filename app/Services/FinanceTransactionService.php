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

namespace App\Services;

use App\Models\FinanceAccount;
use App\Models\FinanceTransaction;

class FinanceTransactionService
{

    public function updateBalance(FinanceAccount $account, $amount): void
    {
        $account->balance += $amount;
        $account->save();
    }

    public function handleTransaction(array $newData, array $oldData = []): void
    {
        // Cek apakah ada data lama dan akun keuangan berubah
        if (isset($oldData['account_id'])) {
            // Kembalikan saldo akun lama
            $oldAccount = FinanceAccount::findOrFail($oldData['account_id']);
            $this->updateBalance($oldAccount, -$oldData['amount']);
        }

        // Jika tidak ada akun finansial baru, hapus transaksi lama dan berhenti
        if (empty($newData['account_id'])) {
            if (isset($oldData['ref_id'])) {
                FinanceTransaction::where('ref_id', $oldData['ref_id'])
                    ->where('ref_type', $oldData['ref_type'])
                    ->delete();
            }
            return;
        }

        // Perbarui saldo akun baru
        $account = FinanceAccount::findOrFail($newData['account_id']);
        $this->updateBalance($account, $newData['amount']);

        // Buat transaksi baru atau perbarui transaksi
        FinanceTransaction::updateOrCreate(
            [
                'ref_id' => $newData['ref_id'],
                'ref_type' => $newData['ref_type'],
            ],
            [
                'account_id' => $newData['account_id'],
                'datetime' => $newData['datetime'],
                'amount' => $newData['amount'],
                'type' => $newData['type'],
                'notes' => $newData['notes'],
            ]
        );
    }

    public function reverseTransaction($ref_id, $ref_type): void
    {
        $trx = FinanceTransaction::where('ref_id', $ref_id)
            ->where('ref_type', $ref_type)
            ->first();

        if ($trx) {
            $account = $trx->account;
            $this->updateBalance($account, -1 * $trx->amount);
            $trx->delete();
        }
    }
}
