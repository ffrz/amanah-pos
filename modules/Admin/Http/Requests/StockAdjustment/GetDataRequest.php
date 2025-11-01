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

namespace Modules\Admin\Http\Requests\StockAdjustment;

use Modules\Admin\Http\Requests\DefaultGetDataRequest;

class GetDataRequest extends DefaultGetDataRequest
{
    /**
     * Dapatkan aturan validasi yang berlaku untuk permintaan.
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'order_by' => 'sometimes|string|in:id'
        ]);
    }

    /**
     * Siapkan data untuk validasi, termasuk penetapan nilai default.
     * Kita menggunakan method ini agar default value bisa diakses di service.
     */
    protected function prepareForValidation(): void
    {
        $filter = $this->input('filter', []);

        $this->merge([
            'filter' => [
                'search' => $filter['search'] ?? null,
                'start_date'   => $filter['start_date'] ?? null,
                'end_date'  => $filter['end_date'] ?? null,
                'type'   => $filter['type'] ?? 'all',
                'status' => $filter['status'] ?? 'all',
            ],
        ]);
    }
}
