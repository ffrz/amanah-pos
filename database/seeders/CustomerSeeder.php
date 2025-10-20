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

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\Customer;
use App\Models\CustomerAccount;
use Faker\Factory as Faker;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID'); // pakai locale Indonesia jika perlu
        $password = Hash::make('12345');

        for ($i = 1; $i <= 10; $i++) {
            $phone = $faker->unique()->numerify('08##########');
            $code = $faker->randomElement(['2025']) . str_pad($i, 3, '0', STR_PAD_LEFT);

            Customer::create([
                'type' => Customer::Type_General,
                'code' => $code,
                'name' => $faker->firstName('male') . ' ' . $faker->lastName('male'),
                'phone' => $phone,
                'address' => $faker->address,
                'wallet_balance' => 0, //$faker->randomNumber(3) * 500,
                'active' => $faker->boolean(90),

                'password' => $password,
                'remember_token' => Str::random(10),
                'last_login_datetime' => null,
                'last_activity_description' => '',
                'last_activity_datetime' => null,

                'created_at' => now(),
                'updated_at' => now(),
                'created_by' => 1,
                'updated_by' => 1,
            ]);
        }
    }
}
