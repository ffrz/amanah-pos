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

class StoreRequest extends FormRequest
{
    /**
     * Dapatkan aturan validasi yang berlaku untuk permintaan.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'datetime' => 'required|date',

            // Akun Sumber (Laci Kasir)
            'source_finance_account_id' => 'required|integer|exists:finance_accounts,id',

            // Akun Tujuan (Brankas/Bank) - Harus beda dengan sumber
            'target_finance_account_id' => 'required|integer|exists:finance_accounts,id|different:source_finance_account_id',

            'amount' => 'required|numeric|min:1',
            'notes' => 'nullable|string|max:500',

            // Validasi file gambar (Max 2MB)
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',

            // Opsional (Nullable sesuai migrasi)
            'terminal_id' => 'nullable|integer|exists:cashier_terminals,id',
            'cashier_session_id' => 'nullable|integer|exists:cashier_sessions,id',
        ];
    }

    public function messages()
    {
        return [
            'target_finance_account_id.different' => 'Akun kas tujuan tidak boleh sama dengan akun kas sumber.',
            'amount.min' => 'Jumlah setoran minimal 1 rupiah.',
        ];
    }
}
