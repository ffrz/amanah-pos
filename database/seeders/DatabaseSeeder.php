<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $this->call([
                UserSeeder::class,
                CustomerSeeder::class,
                SupplierSeeder::class,

                ProductCategorySeeder::class,
                ProductSeeder::class,

                OperationalCostCategorySeeder::class,
                OperationalCostSeeder::class,
            ]);
        });
    }
}
