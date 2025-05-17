<?php

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
                'name' => 'Kas Rek 1',
                'type' => FinanceAccount::Type_Bank,
                'bank' => 'BCA',
                'number' => '12345678',
                'holder' => 'Muhammad',
                'balance' => 0,
            ],
        ]);
    }
}
