<?php

/**
 * Proprietary Software / Perangkat Lunak Proprietary
 * Copyright (c) 2025 Fahmi Fauzi Rahman. All rights reserved.
 * * EN: Unauthorized use, copying, modification, or distribution is prohibited.
 * ID: Penggunaan, penyalinan, modifikasi, atau distribusi tanpa izin dilarang.
 * * See the LICENSE file in the project root for full license information.
 * Lihat file LICENSE di root proyek untuk informasi lisensi lengkap.
 * * GitHub: https://github.com/ffrz
 * Email: fahmifauzirahman@gmail.com
 */

namespace Modules\Admin\Http\Requests\CashierCashDrop;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmRequest extends FormRequest
{
    /**
     * Dapatkan aturan validasi yang berlaku untuk permintaan.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules()
    {
        return [
            // Validasi ID transaksi setoran
            'id' => 'required|integer|exists:cashier_cash_drops,id',

            // Validasi aksi: hanya boleh 'accept' atau 'reject'
            'action' => 'required|string|in:accept,reject'
        ];
    }

    /**
     * Siapkan data untuk validasi.
     * Mengambil ID dari route param atau input body.
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'id' => $this->route('id') ?? $this->input('id'),
        ]);
    }
}
