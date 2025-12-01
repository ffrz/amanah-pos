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

namespace Modules\Admin\Http\Requests\SupplierWalletTransaction;

use Illuminate\Foundation\Http\FormRequest;

class AdjustmentRequest extends FormRequest
{
    /**
     * Dapatkan aturan validasi yang berlaku untuk permintaan.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules()
    {
        if ($this->isMethod('GET')) return [];

        return [
            'id' => 'nullable|integer|exists:supplier_wallet_transactions,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'new_wallet_balance' => 'required|numeric',
            'notes' => 'nullable|string|max:255',
        ];
    }

    /**
     * Siapkan data untuk validasi.
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'id' => $this->id ?? null,
            'supplier_id' => $this->supplier_id ?? null,
            'notes' => $this->notes ?? '',
        ]);
    }
}
