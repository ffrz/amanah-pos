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

namespace Modules\Admin\Http\Requests\SalesOrder;

use Illuminate\Foundation\Http\FormRequest;

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
            'id' => 'nullable|integer|exists:sales_orders,id',
            'customer_id' => 'nullable|integer|exists:customers,id',
            'notes' => 'nullable|string|max:200',
            'total_discount' => 'nullable|numeric|min:0',
            'datetime' => 'nullable|date',
        ];
    }


    /**
     * Prepare data for validation, including the default values.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'id'      => $this->id ?? null,
            'notes'    => $this->notes ?? '',
            'datetime' => $this->datetime ?? now(),
        ]);
    }
}
