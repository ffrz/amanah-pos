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

namespace Modules\Admin\Http\Requests\SupplierLedger;

use App\Models\SupplierLedger;
use Illuminate\Foundation\Http\FormRequest;

class SaveRequest extends FormRequest
{
    /**
     * Dapatkan aturan validasi yang berlaku untuk permintaan.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules()
    {
        return [
            'id' => 'nullable|integer|exists:supplier_ledgers,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'finance_account_id' => 'sometimes|nullable|exists:finance_accounts,id',
            'datetime'   => 'required|date',
            'type'       => 'required|in:' . implode(',', array_keys(SupplierLedger::Types)),
            'amount'     => 'required|numeric',
            'image_path' => 'nullable|string',
            'image'      => 'nullable|image|max:15120',
            'notes'      => 'nullable|string|max:255',
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
            'finance_account_id' => $this->finance_account_id ?? null,
            'image_path' => $this->image_path ?? '',
            'image' => $this->image ?? null,
            'notes' => $this->notes ?? '',
        ]);
    }
}
