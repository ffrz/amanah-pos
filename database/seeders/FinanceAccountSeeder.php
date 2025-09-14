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

class FinanceAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('finance_accounts')->insert([
            [
                'name' => 'Kas POS',
                'type' => FinanceAccount::Type_Cash,
                'balance' => 0,
            ],
            [
                'name' => 'Kas Tunai 1',
                'type' => FinanceAccount::Type_Cash,
                'balance' => 0,
            ],
        ]);
        DB::table('finance_accounts')->insert([
            [
                'name' => 'Rek BCA Muhammad',
                'type' => FinanceAccount::Type_Bank,
                'bank' => 'BCA',
                'number' => '12345678',
                'holder' => 'Muhammad',
                'balance' => 0,
                'has_wallet_access' => true,
            ],
        ]);
                DB::table('finance_accounts')->insert([
            [
                'name' => 'Rek Mandiri Mahad',
                'type' => FinanceAccount::Type_Bank,
                'bank' => 'Mandiri',
                'number' => '123123123123',
                'holder' => 'Amanah ID',
                'balance' => 0,
                'has_wallet_access' => true,
            ],
        ]);
    }
}
