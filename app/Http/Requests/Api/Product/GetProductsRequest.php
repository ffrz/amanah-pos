<?php

namespace App\Http\Requests\Api\Product;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;

class GetProductsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'order_by' => ['nullable', 'string', 'in:id,name,barcode,price,cost,type'], // Kolom yang bisa diurutkan
            'order_type' => ['nullable', 'string', 'in:asc,desc'],

            // Filters (semua filter, termasuk search, ada di sini)
            'filter' => [
                'nullable',
                'array',
            ],

            // ini gak jalan, skip aja dulu karena di product service juga sudah ada validasi
            // 'filter.category_id' => ['nullable', 'integer', 'exists:product_categories,id'],
            // 'filter.supplier_id' => ['nullable', 'integer', 'exists:suppliers,id'],
            // 'filter.type' => ['nullable', 'string', 'in:' . implode(',', array_keys(Product::Types))],
            // 'filter.stock_status' => ['nullable', 'string', 'in:low,out,over,ready'],
            // 'filter.status' => ['nullable', 'string', 'in:active,inactive'],
            // 'filter.search' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Get custom messages for validation errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'filter.category_id.exists' => 'The selected category does not exist.', // Perubahan di sini
            'filter.supplier_id.exists' => 'The selected supplier does not exist.', // Perubahan di sini
            'order_by.in' => 'The selected sort by option is invalid.',
            'order_type.in' => 'The selected order type is invalid.',
        ];
    }
}
