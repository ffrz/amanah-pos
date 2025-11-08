<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $numberOfRecords = 10000; // Target 10 ribu
        $chunkSize = 1000; // Ukuran chunk yang aman
        $iterations = ceil($numberOfRecords / $chunkSize);

        $this->command->info("--- Memulai Seeding Products ($numberOfRecords baris) ---");

        // 1. NONAKTIFKAN KEYS & TRUNCATE (Pembersihan cepat)
        Schema::disableForeignKeyConstraints();
        // DB::table('products')->truncate();
        Schema::enableForeignKeyConstraints();

        $progressBar = $this->command->getOutput()->createProgressBar($numberOfRecords);
        $progressBar->start();

        $factory = Product::factory();
        $insertedCount = 0;

        for ($i = 0; $i < $iterations; $i++) {
            $chunkData = [];

            // 2. Kumpulkan data sebanyak chunkSize
            for ($j = 0; $j < $chunkSize; $j++) {
                if ($insertedCount >= $numberOfRecords) {
                    break 2;
                }

                // Gunakan make() dan toArray() untuk mendapatkan data mentah
                $data = $factory->make()->toArray();

                // Pastikan format datetime aman untuk MySQL
                if (isset($data['created_at'])) {
                    $data['created_at'] = \Illuminate\Support\Carbon::parse($data['created_at'])->toDateTimeString();
                }
                if (isset($data['updated_at'])) {
                    $data['updated_at'] = \Illuminate\Support\Carbon::parse($data['updated_at'])->toDateTimeString();
                }
                unset($data['type_label']);

                $chunkData[] = $data;
                $insertedCount++;
            }

            // 3. Batch Insert (satu query untuk 1000 baris)
            if (!empty($chunkData)) {
                DB::table('products')->insert($chunkData);
                $progressBar->advance(count($chunkData));
            }
        }

        $progressBar->finish();
        $this->command->newLine();
        $this->command->info("✅ $numberOfRecords produk berhasil dibuat.");
    }
}
