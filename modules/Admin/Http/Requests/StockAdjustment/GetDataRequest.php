<?php

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
                'year'   => $filter['year'] ?? 'all',
                'month'  => $filter['month'] ?? 'all',
                'type'   => $filter['type'] ?? 'all',
                'status' => $filter['status'] ?? 'all',
            ],
        ]);
    }
}
