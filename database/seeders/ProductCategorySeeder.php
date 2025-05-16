<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('product_categories')->insert([
            [
                'name' => 'Bahan Bakar',
                'description' => 'Bahan bakar untuk mesin cuci',
            ],
            [
                'name' => 'Bahan Pembersih',
                'description' => 'Bahan pembersih untuk mesin cuci',
            ],
            [
                'name' => 'Bahan Pelumas',
                'description' => 'Bahan pelumas untuk mesin cuci',
            ],
        ]);
    }
}
