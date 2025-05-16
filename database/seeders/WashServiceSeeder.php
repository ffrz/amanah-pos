<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WashServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('wash_services')->insert([
            [
                'name' => 'Cuci Luar - Mobil Kecil',
                'price' => 20000,
                'duration' => 15,
            ],
            [
                'name' => 'Cuci Luar - Mobil Sedang',
                'price' => 25000,
                'duration' => 20,
            ],
            [
                'name' => 'Cuci Luar - Mobil Besar',
                'price' => 30000,
                'duration' => 25,
            ],
            [
                'name' => 'Cuci Luar + Interior - Mobil Kecil',
                'price' => 50000,
                'duration' => 30,
            ],
            [
                'name' => 'Cuci Luar + Interior - Mobil Sedang',
                'price' => 60000,
                'duration' => 35,
            ],
            [
                'name' => 'Cuci Luar + Interior - Mobil Besar',
                'price' => 70000,
                'duration' => 40,
            ],
        ]);
    }
}
