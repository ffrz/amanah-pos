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

namespace Modules\Admin\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates request parameters for retrieving paginated and filtered
 * operational cost categories.
 */
class DefaultGetDataRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'order_by' => 'nullable|string',
            'order_type' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', Rule::in([10, 25, 50, 100])],
            'filter' => ['nullable', 'array'],

        ];
    }

    /**
     * Prepare data for validation, including the default values and cleaning filters.
     */
    protected function prepareForValidation(): void
    {

        $this->merge([
            'order_by'   => $this->order_by ?? 'id',
            'order_type' => $this->order_type ?? 'desc',
            'per_page'   => $this->per_page ?? 10,
            'filter'     => $this->filter ?? [],
        ]);
    }
}
