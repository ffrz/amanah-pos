<?php

namespace Modules\Admin\Http\Requests\StockMovement;

use Illuminate\Foundation\Http\FormRequest;

class GetDataRequest extends FormRequest
{
    /**
     * Dapatkan aturan validasi yang berlaku untuk permintaan.
     */
    public function rules(): array
    {
        return [
            'order_by' => ['sometimes', 'string', 'in:created_at,product_id'], // Tambahkan kolom yang valid
            'order_type' => ['sometimes', 'string', 'in:asc,desc'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'product_id' => ['sometimes', 'integer', 'min:0'],
            'filter.search' => ['nullable', 'string', 'max:100'],
            'filter.ref_type' => ['nullable', 'string', 'max:50'],
            'filter.year' => ['nullable', 'string', 'max:4'],
            'filter.month' => ['nullable', 'string', 'max:2'],
        ];
    }

    /**
     * Siapkan data untuk validasi, termasuk penetapan nilai default.
     * Kita menggunakan method ini agar default value bisa diakses di service.
     */
    protected function prepareForValidation(): void
    {
        $filter = $this->input('filter', []);

        $this->merge([
            'order_by' => $this->input('order_by', 'created_at'),
            'order_type' => $this->input('order_type', 'desc'),
            'per_page' => $this->input('per_page', 10),
            'product_id' => $this->input('product_id', null),
            'filter' => [
                'search' => $filter['search'] ?? null,
                'ref_type' => $filter['ref_type'] ?? 'all',
                'year' => $filter['year'] ?? 'all',
                'month' => $filter['month'] ?? 'all',
            ],
        ]);
    }
}
