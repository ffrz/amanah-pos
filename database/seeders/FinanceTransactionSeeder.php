<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FinanceTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FinanceTransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $numberOfRecords = 1000000; // Target
        $chunkSize = 5000; // Ukuran chunk yang baik untuk insert masal
        $iterations = ceil($numberOfRecords / $chunkSize);

        $this->command->info("--- Memulai Seeding FinanceTransactions ($numberOfRecords baris) ---");

        // Opsional: Nonaktifkan Foreign Keys untuk Insert Masal (Mempercepat proses)
        Schema::disableForeignKeyConstraints();
        // DB::table('finance_transactions')->truncate(); // Bersihkan tabel
        Schema::enableForeignKeyConstraints();

        $progressBar = $this->command->getOutput()->createProgressBar($numberOfRecords);
        $progressBar->start();

        $factory = FinanceTransaction::factory();
        $insertedCount = 0;
        $datePart = now()->format('ymd');
        $prefix = 'FTX'; // Ambil prefix langsung

        // Ambil ID maksimum yang sudah ada (base number)
        $searchPattern = "{$prefix}-{$datePart}-%";

        // Cari kode maksimum yang ada hari ini (diurutkan secara string menurun)
        $maxCode = DB::table('finance_transactions')
            ->where('code', 'like', $searchPattern)
            ->orderBy('code', 'desc')
            ->limit(1)
            ->value('code');

        $transactionNumber = 0;

        if ($maxCode) {
            // Jika kode ditemukan (contoh: FTX-251108-010025)
            // Ambil 6 digit terakhir, konversi ke integer
            // Gunakan substr(string, start, length) atau substr(string, offset)
            $lastSequence = (int) substr($maxCode, -6);
            $transactionNumber = $lastSequence;
            $this->command->warn("Melanjutkan nomor urut dari: {$maxCode}");
        } else {
            // Jika tidak ada kode hari ini, mulai dari 0.
            $this->command->info("Memulai nomor urut baru untuk hari ini.");
        }

        for ($i = 0; $i < $iterations; $i++) {
            $chunkData = [];

            for ($j = 0; $j < $chunkSize; $j++) {
                if ($insertedCount >= $numberOfRecords) {
                    break 2;
                }

                $transactionNumber++; // Tingkatkan nomor transaksi untuk setiap baris
                $sequence = str_pad($transactionNumber, 6, '0', STR_PAD_LEFT); // Asumsi padding 6 digit
                $code = "{$prefix}-{$datePart}-{$sequence}";

                // Generate data, lalu timpa kolom 'code'
                $data = $factory->make()->toArray();
                $data['code'] = $code; // Masukkan kode yang unik dan berurutan

                if (isset($data['created_at'])) {
                    $data['created_at'] = \Illuminate\Support\Carbon::parse($data['created_at'])->toDateTimeString();
                }
                if (isset($data['updated_at'])) {
                    $data['updated_at'] = \Illuminate\Support\Carbon::parse($data['updated_at'])->toDateTimeString();
                }
                if (isset($data['datetime'])) {
                    $data['datetime'] = \Illuminate\Support\Carbon::parse($data['datetime'])->toDateTimeString();
                }

                // ini ter append otomatis, harus dibuang agar tidak error
                unset($data['ref_type_label']);
                unset($data['type_label']);

                $chunkData[] = $data;
                $insertedCount++;
            }

            if (!empty($chunkData)) {
                DB::table('finance_transactions')->insert($chunkData);
                $progressBar->advance(count($chunkData));
            }
        }

        $progressBar->finish();
        $this->command->newLine();
        $this->command->info("✅ $numberOfRecords transaksi keuangan berhasil dibuat.");
    }
}
