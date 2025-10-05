<?php

/**
 * Proprietary Software / Perangkat Lunak Proprietary
 * Copyright (c) 2025 Fahmi Fauzi Rahman. All rights reserved.
 * * EN: Unauthorized use, copying, modification, or distribution is prohibited.
 * ID: Penggunaan, penyalinan, modifikasi, atau distribusi tanpa izin dilarang.
 * * Class ini menangani semua validasi untuk menyimpan Terminal Kasir.
 */

namespace Modules\Admin\Http\Requests\CashierSession;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OpenRequest extends FormRequest
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
            'cashier_terminal_id' => [
                'required',
                'exists:cashier_terminals,id',
                // Pastikan cash register tidak sedang memiliki sesi aktif
                Rule::unique('cashier_sessions', 'cashier_terminal_id')->where(function ($query) {
                    return $query->where('is_closed', false);
                }),
            ],
            'opening_notes' => 'nullable|string|max:200',
        ];

        return $rules;
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'opening_notes' => $this->opening_notes ?? '',
        ]);
    }
}
