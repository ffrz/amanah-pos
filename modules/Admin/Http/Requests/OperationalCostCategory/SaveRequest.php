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

namespace Modules\Admin\Http\Requests\OperationalCostCategory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * File ini menangani validasi input untuk Kategori Biaya Operasional.
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
        $itemId = $this->route('id') ?? $this->id;

        return [
            'id' => 'nullable|integer|exists:operational_cost_categories,id',
            'name' => [
                'required',
                'max:255',
                Rule::unique('operational_cost_categories', 'name')->ignore($itemId),
            ],
            'description' => 'nullable|max:200',
        ];
    }

    protected function preapareForValidation(): void
    {
        $this->merge([
            'id'      => $this->id ?? null,
        ]);
    }
}
