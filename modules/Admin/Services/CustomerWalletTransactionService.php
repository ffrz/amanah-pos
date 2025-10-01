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

use App\Models\Customer;
use App\Models\CustomerWalletTransaction;

class CustomerWalletTransactionService
{

    public static function addToWalletBalance(Customer $customer, $amount): void
    {
        $lockedCustomer = Customer::where('id', $customer->id)->lockForUpdate()->firstOrFail();
        $lockedCustomer->wallet_balance += $amount;
        $lockedCustomer->save();
    }

    // WARNING: Tidak mendukung pengeditan transaksi!!
    public static function handleTransaction(array $newData)
    {
        // Perbarui saldo akun baru
        $account = Customer::findOrFail($newData['customer_id']);
        static::addToWalletBalance($account, $newData['amount']);

        // Buat transaksi baru
        return CustomerWalletTransaction::create(
            [
                'ref_id' => $newData['ref_id'] ?? null,
                'ref_type' => $newData['ref_type'] ?? null,
                'customer_id' => $newData['customer_id'],
                'finance_account_id' => $newData['finance_account_id'] ?? null,
                'datetime' => $newData['datetime'],
                'amount' => $newData['amount'],
                'type' => $newData['type'],
                'notes' => $newData['notes'],
                'image_path' => $newData['image_path'] ?? null,
            ]
        );
    }

    public static function reverseTransaction($ref_id, $ref_type)
    {
        $trx = CustomerWalletTransaction::where('ref_id', $ref_id)
            ->where('ref_type', $ref_type)
            ->first();

        if ($trx) {
            static::addToWalletBalance($trx->customer, -$trx->amount);
            $trx->delete();
        }

        return  $trx;
    }
}
