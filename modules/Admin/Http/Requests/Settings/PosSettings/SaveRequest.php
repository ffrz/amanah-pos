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

namespace Modules\Admin\Http\Requests\Settings\PosSettings;

use Illuminate\Foundation\Http\FormRequest;

class SaveRequest extends FormRequest
{
    /**
     * Dapatkan aturan validasi yang berlaku untuk request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        if ($this->isMethod('GET')) {
            return [];
        }

        return [
            'default_payment_mode' => ['required', 'string'],
            'default_print_size' => ['required', 'string'],
            'after_payment_action' => ['required', 'string'],
            'foot_note' => ['nullable', 'string', 'max:200'],
            'allow_negative_inventory' => ['nullable', 'boolean'],
            'allow_credit_limit' => ['nullable', 'boolean'],
            'allow_selling_at_loss' => ['nullable', 'boolean'],
        ];
    }


    protected function prepareForValidation(): void
    {
        $this->merge([
            'foot_note' => $this->foot_note ?? '',
        ]);
    }
}
