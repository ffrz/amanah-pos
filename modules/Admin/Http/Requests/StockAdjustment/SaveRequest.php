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
use Illuminate\Validation\Rule;


class SaveRequest extends FormRequest
{

    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:stock_adjustments,id',
            'datetime' => ['required', 'date'], // atau gunakan: 'date_format:Y-m-d H:i:s'
            'type' => [
                'required',
                Rule::in(array_keys(StockAdjustment::Types)),
            ],
            'notes' => 'nullable|string|max:1000',
            'details' => 'required|array',
            'action' => 'nullable|string|in:close,cancel,save',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'notes' => $this->notes ?? '',
        ]);
    }
}
