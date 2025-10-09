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

namespace Modules\Admin\Http\Requests\StockMovement;

use Modules\Admin\Http\Requests\DefaultGetDataRequest;

class GetDataRequest extends DefaultGetDataRequest
{
    /**
     * Dapatkan aturan validasi yang berlaku untuk permintaan.
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'order_by' => 'sometimes|string|in:id,created_at,product_id'
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
                'product_id' => $filter['product_id'] ?? null,
                'search' => $filter['search'] ?? null,
                'ref_type' => $filter['ref_type'] ?? 'all',
                'year' => $filter['year'] ?? 'all',
                'month' => $filter['month'] ?? 'all',
            ],
        ]);
    }
}
