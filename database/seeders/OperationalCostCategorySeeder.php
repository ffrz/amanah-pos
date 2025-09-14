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
use Illuminate\Support\Facades\DB;

class OperationalCostCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('operational_cost_categories')->insert([
            [
                'name' => 'Listrik',
                'description' => 'Biaya Tagihan Listrik',
            ],
            [
                'name' => 'Telepon',
                'description' => 'Biaya Tagihan Telepon',
            ],
            [
                'name' => 'Sewa Tempat',
                'description' => 'Biaya Sewa Tempat',
            ],
            [
                'name' => 'Gaji Karyawan',
                'description' => 'Biaya Gaji Karyawan',
            ],
            [
                'name' => 'Pajak',
                'description' => 'Biaya Pajak',
            ],
            [
                'name' => 'Biaya Lain-lain',
                'description' => 'Biaya Lain-lain',
            ],
        ]);
    }
}
