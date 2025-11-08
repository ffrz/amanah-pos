<?php

namespace Database\Factories;

use App\Models\FinanceTransaction;
use App\Models\FinanceAccount; // Asumsikan model ini ada
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class FinanceTransactionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = FinanceTransaction::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        // 1. Ambil ID akun yang sudah ada (Pastikan FinanceAccountSeeder sudah dijalankan)
        // Ambil ID dari 10 akun pertama (untuk variasi yang cukup)
        $accountIds = FinanceAccount::pluck('id')->take(10)->toArray();
        if (empty($accountIds)) {
            // Fallback jika belum ada akun
            $accountIds = [1];
        }

        // 2. Ambil tipe transaksi dan tipe referensi yang valid dari Model
        $types = array_keys(FinanceTransaction::Types);
        $refTypes = array_keys(FinanceTransaction::RefTypeModels);

        // Pilih ref_type secara acak
        $randomRefType = $this->faker->randomElement($refTypes);

        // Pilih ID ref yang ada (asumsi ada data di tabel referensi, jika tidak, pakai ID palsu)
        $refId = $this->faker->numberBetween(1, 500); // ID palsu untuk skalabilitas

        // Tentukan nilai amount berdasarkan tipe
        $type = $this->faker->randomElement($types);
        $amount = $this->faker->randomFloat(2, 5000, 1000000); // 5.000,00 hingga 1.000.000,00

        return [
            // Kolom Wajib
            'account_id' => $this->faker->randomElement($accountIds),
            // 'code' => FinanceTransaction::generateTransactionCode(), // Menggunakan Trait
            'datetime' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'type' => $type,
            'amount' => $amount,

            // Kolom Relasi (Morphs)
            'ref_type' => $randomRefType,
            'ref_id' => $refId, // Angka acak sebagai simulasi ID

            // Kolom Opsional
            'notes' => $this->faker->sentence(),
            'image_path' => $this->faker->boolean(10) ? 'images/transaction-' . $this->faker->uuid() . '.jpg' : null,

            // Timestamps
            'created_at' => now(),
            'updated_at' => now(),
            // created_by/updated_by diurus oleh BaseModel/Trait jika ada
        ];
    }

    /**
     * Tambahkan State untuk mengizinkan penambahan 'code' di Seeder
     * @param string $code
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function withCode(string $code)
    {
        return $this->state(fn(array $attributes) => [
            'code' => $code,
        ]);
    }
}
