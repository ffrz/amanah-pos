<?php

namespace Modules\Admin\Http\Requests\Settings\CompanyProfile;

use Illuminate\Foundation\Http\FormRequest;

class SaveRequest extends FormRequest
{

    /**
     * Tentukan apakah pengguna diizinkan membuat permintaan ini.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Dapatkan aturan validasi yang berlaku untuk permintaan.
     */
    public function rules(): array
    {
        return [
            'name'       => 'required|string|min:2|max:100',
            'headline'   => 'nullable|string|max:200',
            'phone'      => 'nullable|string|regex:/^(\+?\d{1,4})?[\s.-]?\(?\d{1,4}\)?[\s.-]?\d{1,4}[\s.-]?\d{1,9}$/|max:40',
            'address'    => 'nullable|string|max:500',
            'logo_path'  => 'nullable|max:500',
            'logo_image' => 'nullable|image|max:5120',
        ];
    }

    /**
     * Siapkan data untuk validasi, termasuk penetapan nilai default.
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
