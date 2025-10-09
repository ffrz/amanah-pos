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

namespace Modules\Admin\Http\Requests\CashierSession;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CloseRequest extends FormRequest
{
    /**
     * Dapatkan aturan validasi yang berlaku untuk permintaan.
     */
    public function rules(): array
    {
        if ($this->isMethod('GET')) {
            return [];
        }

        $rules = [
            'closing_notes' => 'nullable|string|max:200',
        ];

        return $rules;
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'closing_notes' => $this->closing_notes ?? '',
        ]);
    }
}
