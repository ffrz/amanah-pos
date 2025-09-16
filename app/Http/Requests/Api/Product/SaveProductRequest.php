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

namespace App\Http\Requests\Api\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule; // Import Rule
use App\Models\Product; // Import Product model untuk Product::Types

class SaveProductRequest extends FormRequest
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
        $productId = $this->input('id');

        return [
            'category_id' => ['nullable', Rule::exists('product_categories', 'id')],
            'supplier_id' => ['nullable', Rule::exists('suppliers', 'id')],
            'type' => ['nullable', Rule::in(array_keys(Product::Types))],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products', 'name')->ignore($productId),
            ],
            'description' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'barcode'   => ['sometimes', 'nullable', 'string', 'max:255'],
            'uom'       => ['sometimes', 'nullable', 'string', 'max:255'],
            'stock'     => ['sometimes', 'numeric'],
            'min_stock' => ['sometimes', 'numeric'],
            'max_stock' => ['sometimes', 'numeric'],
            'cost'      => ['sometimes', 'numeric'],
            'price'     => ['sometimes', 'numeric'],
            'price_2'   => ['sometimes', 'numeric'],
            'price_3'   => ['sometimes', 'numeric'],
            'active'    => ['sometimes', 'boolean'],
            'price_editable' => ['nullable', 'boolean'],
            'notes'     => ['sometimes', 'nullable', 'string', 'max:1000'],
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
            'category_id.exists' => 'Kategori yang dipilih tidak ditemukan.',
            'supplier_id.exists' => 'Pemasok yang dipilih tidak ditemukan.',
            'name.unique' => 'Nama produk sudah digunakan.',
        ];
    }
}
