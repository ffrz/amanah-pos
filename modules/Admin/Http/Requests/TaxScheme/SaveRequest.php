<?php

/**
 * Proprietary Software / Perangkat Lunak Proprietary
 * Copyright (c) 2025 Fahmi Fauzi Rahman. All rights reserved.
 * * EN: Unauthorized use, copying, modification, or distribution is prohibited.
 * ID: Penggunaan, penyalinan, modifikasi, atau distribusi tanpa izin dilarang.
 * * See the LICENSE file in the project root for full license information.
 * Lihat file LICENSE di root proyek untuk informasi lisensi lengkap.
 * * GitHub: https://github.com/ffrz
 * Email: fahmifauzirahman@gmail.com
 */

namespace Modules\Admin\Http\Requests\TaxScheme;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * File ini menangani validasi input untuk Skema Pajak.
 * Ia memastikan bahwa data yang masuk memenuhi aturan yang diperlukan
 * sebelum diteruskan ke layanan atau model.
 */
class SaveRequest extends FormRequest
{
    /**
     * Dapatkan aturan validasi yang berlaku untuk permintaan.
     * Aturan unik di sini menggunakan ID dari permintaan untuk mengabaikan
     * item saat pembaruan.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        // Mendapatkan ID dari route (untuk edit) atau dari payload (untuk submit form)
        $itemId = $this->route('id') ?? $this->id;

        return [
            'id' => 'nullable|integer|exists:tax_schemes,id',

            // Nama wajib diisi, max 100, dan unik (mengabaikan ID saat Edit)
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('tax_schemes', 'name')->ignore($itemId),
            ],

            // Tarif persentase wajib, numerik, dan minimal 0
            'rate_percentage' => 'required|numeric|min:0',

            // Otoritas pajak opsional, string, max 50
            'tax_authority' => 'nullable|string|max:50',

            // Kolom boolean (q-toggle)
            'is_inclusive' => 'required|boolean',
            'active' => 'required|boolean',

            // Deskripsi opsional, maksimal 200 karakter
            'description' => 'nullable|string|max:200',
        ];
    }

    /**
     * Tentukan apakah pengguna diizinkan untuk membuat permintaan ini.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Sesuaikan dengan logic otorisasi yang Anda gunakan
        // Contoh: return $this->user()->can('manage-tax-schemes');
        return true;
    }

    /**
     * Persiapan data sebelum validasi.
     * Ini memastikan bahwa ID dimasukkan jika tidak ada di route (misalnya saat duplikasi).
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'id' => $this->id ?? null,
            // Jika ada field boolean yang mungkin tidak terkirim saat false (seperti dari checkbox HTML biasa),
            // Anda bisa tambahkan default value di sini. Namun, Quasar/Inertia biasanya menangani boolean dengan baik.
        ]);
    }
}
