<?php

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
