<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class OperationalCostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\OperationalCost::factory(20)->create();
    }
}
