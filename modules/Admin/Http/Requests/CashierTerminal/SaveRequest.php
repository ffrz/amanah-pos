<?php

/**
 * Proprietary Software / Perangkat Lunak Proprietary
 * Copyright (c) 2025 Fahmi Fauzi Rahman. All rights reserved.
 * * EN: Unauthorized use, copying, modification, or distribution is prohibited.
 * ID: Penggunaan, penyalinan, modifikasi, atau distribusi tanpa izin dilarang.
 * * Class ini menangani semua validasi untuk menyimpan Terminal Kasir.
 */

namespace Modules\Admin\Http\Requests\CashierTerminal;

use Illuminate\Foundation\Http\FormRequest;

class SaveRequest extends FormRequest
{
    /**
     * Dapatkan aturan validasi yang berlaku untuk permintaan.
     */
    public function rules(): array
    {
        // Mendapatkan ID terminal baik dari route atau body request
        $terminalId = $this->route('id') ?? $this->input('id');

        $rules = [
            'id'       => 'nullable|integer|exists:cashier_terminals,id',
            'name'     => 'required|string|max:40|unique:cashier_terminals,name' . ($terminalId ? ',' . $terminalId : ''),
            'location' => 'nullable|max:255',
            'notes'    => 'nullable|max:255',
            'active'   => 'required|boolean',
            'finance_account_id' => 'nullable|string',
            // 'finance_account_id' => 'required_unless:finance_account_id,new|nullable|exists:finance_accounts,id',
        ];

        return $rules;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'id' => $this->id ?? null,
            'finance_account_id' => $this->finance_account_id ?? null,
        ]);
    }
}
