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

namespace Database\Seeders;

use App\Models\FinanceAccount;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CashierTerminalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $account1 = FinanceAccount::create([
            'name' => 'Kas Kasir 1',
            'type' => FinanceAccount::Type_CashierCash,
            'active' => true,
        ]);

        $account2 = FinanceAccount::create([
            'name' => 'Kas Kasir 2',
            'type' => FinanceAccount::Type_CashierCash,
            'active' => true,
        ]);

        DB::table('cashier_terminals')->insert([
            [
                'name' => 'Kasir Toko 1',
                'location' => 'Toko',
                'notes' => 'Auto generated cashier terminal',
                'finance_account_id' => $account1->id,
                'active' => true,
            ],
            [
                'name' => 'Kasir Toko 2',
                'location' => 'Toko',
                'notes' => 'Auto generated cashier terminal',
                'finance_account_id' => $account2->id,
                'active' => true,
            ],
        ]);
    }
}
