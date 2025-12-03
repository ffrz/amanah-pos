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

namespace Modules\Admin\Http\Requests\OperationalCost;

use Illuminate\Foundation\Http\FormRequest;

/**
 * File ini menangani validasi input untuk Biaya Operasional.
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
        return [
            'id'                 => 'nullable|integer|exists:operational_costs,id',
            'finance_account_id' => 'nullable|exists:finance_accounts,id',
            'date'               => 'required|date',
            'category_id'        => 'required|exists:operational_cost_categories,id',
            'description'        => 'required|max:255',
            'amount'             => 'required|numeric|gt:0',
            'notes'              => 'nullable|max:200',
            'image_path'         => 'nullable|string',
            'image'              => 'nullable|image|max:15120',
        ];
    }


    /**
     * Prepare data for validation, including the default values.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'id'      => $this->id ?? null,
            'notes'   => $this->notes ?? '',
        ]);
    }
}
