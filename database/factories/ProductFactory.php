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

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class ProductFactory extends Factory
{
    protected static int $incrementingId = 0;

    public function definition(): array
    {
        if (static::$incrementingId === 0) {
            static::$incrementingId = DB::table('products')->max('id') ?? 0;
        }

        static::$incrementingId++;
        $formattedId = 'P-' . str_pad(static::$incrementingId, 6, '0', STR_PAD_LEFT);

        $cost = $this->faker->numberBetween(1, 1000) * 1000;
        $price = 0.20 * $cost + $cost;

        return [
            'name' => $formattedId,
            'barcode' => $this->faker->ean13,
            'uom' => $this->faker->randomElement(['pcs', 'box', 'kg', 'unit', 'bh', 'pt', 'ps', 'pak', 'dus']),
            'category_id' => DB::table('product_categories')->inRandomOrder()->value('id'),
            'supplier_id' => DB::table('suppliers')->inRandomOrder()->value('id'),
            'description' => $this->faker->sentence,
            'cost' => $cost,
            'price_1' => $price,
            'stock' => 0,
            'min_stock' => $this->faker->randomElement([0, 1, 2, 3, 5, 10, 12, 20, 50]),
            'max_stock' => 0,
            'notes' => '',
            'type' => Product::Type_Stocked,
            'active' => true
        ];
    }
}
