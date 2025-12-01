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

use Modules\Admin\Http\Requests\DefaultGetDataRequest;

class GetDataRequest extends DefaultGetDataRequest
{
    /**
     * Dapatkan aturan validasi yang berlaku untuk permintaan.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $rules = parent::rules();

        return array_merge($rules, [
            // Kolom yang diizinkan untuk sorting
            'order_by'  => 'nullable|string|in:id,code,datetime,amount,status,cashier_id,source_finance_account_id,target_finance_account_id',

            // Tambahan validasi filter spesifik jika perlu (opsional, karena DefaultGetDataRequest biasanya sudah handle basic)
            'filter.search' => 'nullable|string',
            'filter.cashier_id' => 'nullable',
            'filter.status' => 'nullable|string',
            'filter.start_date' => 'nullable|date',
            'filter.end_date' => 'nullable|date',
        ]);
    }
}
