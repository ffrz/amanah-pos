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

namespace Modules\Service\Http\Requests\Technician;

use App\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveRequest extends FormRequest
{
    /**
     * Dapatkan aturan validasi yang berlaku untuk request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $id = $this->input('id');

        $rules = [
            'id' => 'nullable|integer|exists:service_technicians,id',
            'code' => 'required|string|max:40|unique:service_technicians,code' . ($id ? ',' . $id : ''),
            'name' => 'required|max:255',
            'phone' => 'nullable|max:100',
            'address' => 'nullable|max:1000',
            'active'   => 'required|boolean',
        ];

        return $rules;
    }

    /**
     * Dapatkan pesan kesalahan yang disesuaikan.
     *
     * @return array
     */
    public function messages(): array
    {
        return [];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'id' => $this->id ?? null,
            'phone' => $this->phone ?? '',
            'address' => $this->address ?? '',
            'password' => $this->password ?? '',
            'credit_limit' => $this->credit_limit ?? 0,
        ]);
    }
}
