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
        $categories = [];
        for ($i = 1; $i <= 20; $i++) {
            $categories[] = [
                'name' => "Kategori $i",
                'description' => "Deskripsi kategori $i",
            ];
        }
        DB::table('product_categories')->insert($categories);
    }
}
