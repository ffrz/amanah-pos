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

namespace Modules\Admin\Http\Requests\Supplier;

use Illuminate\Foundation\Http\FormRequest;

class SaveRequest extends FormRequest
{
    /**
     * Dapatkan aturan validasi yang berlaku untuk request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $supplierId = $this->input('id');

        $rules = [
            'id' => 'nullable|integer|exists:suppliers,id',
            'name' => 'required|string|max:50|unique:suppliers,name' . ($supplierId ? ',' . $supplierId : ''),
            'phone_1' => 'nullable|max:50',
            'phone_2' => 'nullable|max:50',
            'phone_3' => 'nullable|max:50',
            'bank_account_name_1' => 'nullable|max:50',
            'bank_account_number_1' => 'nullable|max:50',
            'bank_account_holder_1' => 'nullable|max:100',
            'bank_account_name_2' => 'nullable|max:50',
            'bank_account_number_2' => 'nullable|max:50',
            'bank_account_holder_2' => 'nullable|max:100',
            'active' => 'required|boolean',
            'address' => 'nullable|max:200',
            'return_address' => 'nullable|max:200',
            'url_1' => 'nullable|max:512',
            'url_2' => 'nullable|max:512',
            'notes' => 'nullable|max:1000',
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
            'phone_1' => $this->phone_1 ?? '',
            'phone_2' => $this->phone_2 ?? '',
            'phone_3' => $this->phone_3 ?? '',
            'address' => $this->address ?? '',
            'return_address' => $this->return_address ?? '',
            'bank_account_name_1' => $this->bank_account_name_1 ?? '',
            'bank_account_number_1' => $this->bank_account_number_1 ?? '',
            'bank_account_holder_1' => $this->bank_account_holder_1 ?? '',
            'bank_account_name_2' => $this->bank_account_name_2 ?? '',
            'bank_account_number_2' => $this->bank_account_number_2 ?? '',
            'bank_account_holder_2' => $this->bank_account_holder_2 ?? '',
            'url_1' => $this->url_1 ?? '',
            'url_2' => $this->url_2 ?? '',
            'notes' => $this->notes ?? '',
        ]);
    }
}
