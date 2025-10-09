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

use App\Models\StockAdjustment;
use Illuminate\Foundation\Http\FormRequest;


class CreateRequest extends FormRequest
{

    public function rules(): array
    {
        if ($this->isMethod('GET')) return [];

        return [
            'product_ids' => 'required|array',
            'datetime' => 'required|date',
            'type' => 'required|string|in:' . join(",", array_keys(StockAdjustment::Types)),
            'notes' => 'nullable|string|max:200',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'product_ids' => $this->product_ids ?? [],
            'datetime' => $this->datetime ?? date('Y-m-d H:i:s'),
            'type' => $this->type ?? StockAdjustment::Type_StockCorrection,
            'notes' => $this->notes ?? '',
        ]);
    }

    public function messages(): array
    {
        return [
            'product_ids.required' => 'Anda belum memilih produk!',
        ];
    }
}
