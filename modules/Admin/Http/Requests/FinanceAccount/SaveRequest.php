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

namespace Modules\Admin\Http\Requests\FinanceAccount;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\FinanceAccount;

class SaveRequest extends FormRequest
{
    /**
     * Dapatkan aturan validasi yang berlaku untuk permintaan.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules()
    {
        $accountId = $this->route('id') ?? $this->input('id');

        return [
            'id'        => 'nullable|integer|exists:finance_accounts,id',
            'name'      => 'required|string|max:40|unique:finance_accounts,name' . ($accountId ? ',' . $accountId : ''),
            'type'      => 'required|in:' . implode(',', array_keys(FinanceAccount::Types)),
            'bank'      => 'nullable|string|max:40',
            'number'    => 'nullable|string|max:20',
            'holder'    => 'nullable|string|max:100',
            'balance'   => 'required|numeric',
            'active'    => 'required|boolean',
            'show_in_pos_payment'        => 'required|boolean',
            'show_in_purchasing_payment' => 'required|boolean',
            'has_wallet_access'          => 'required|boolean',
            'notes'     => 'nullable|string|max:255',
        ];
    }

    /**
     * Siapkan data untuk validasi.
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'id'      => $this->id ?? null,
            'bank'    => $this->bank ?? '',
            'number'  => $this->number ?? '',
            'holder'  => $this->holder ?? '',
            'notes'   => $this->notes ?? '',
            'balance' => $this->balance ?? 0,
            'active'  => $this->active ?? false,
        ]);
    }
}
