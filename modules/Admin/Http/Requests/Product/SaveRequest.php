<?php

/**
 * Proprietary Software / Perangkat Lunak Proprietary
 * Copyright (c) 2025 Fahmi Fauzi Rahman. All rights reserved.
 * * EN: Unauthorized use, copying, modification, or distribution is prohibited.
 * ID: Penggunaan, penyalinan, modifikasi, atau distribusi tanpa izin dilarang.
 * * See the LICENSE file in the project root for full license information.
 * Lihat file LICENSE di root proyek untuk informasi lisensi lengkap.
 * * GitHub: https://github.com/ffrz
 * Email: fahmifauzirahman@gmail.com
 */

namespace Modules\Admin\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Product;

class SaveRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $productId = $this->input('id');

        return [
            // --- Product Basic Info ---
            'id' => 'nullable|integer|exists:products,id',
            'brand_id' => ['nullable', Rule::exists('product_brands', 'id')],
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
            'uom'         => ['sometimes', 'nullable', 'string', 'max:255'],
            'barcode'     => ['sometimes', 'nullable', 'string', 'max:255'],
            'notes'       => ['sometimes', 'nullable', 'string', 'max:1000'],
            'active'      => ['sometimes', 'boolean'],

            // --- Inventory & Base Cost ---
            'stock'     => ['sometimes', 'numeric'],
            'min_stock' => ['sometimes', 'numeric'],
            'max_stock' => ['sometimes', 'numeric'],
            'cost'      => ['sometimes', 'numeric'],

            // --- Main Product Pricing (Level 1, 2, 3) ---
            'price_editable' => ['nullable', 'boolean'],

            'price_1'        => 'nullable|numeric',
            'price_1_markup' => 'nullable|numeric',
            'price_1_option' => 'nullable|string|in:markup,price',
            'price_1_tiers'  => 'nullable|array',

            'price_2'        => 'nullable|numeric',
            'price_2_markup' => 'nullable|numeric',
            'price_2_option' => 'nullable|string|in:markup,price',
            'price_2_tiers'  => 'nullable|array',

            'price_3'        => 'nullable|numeric',
            'price_3_markup' => 'nullable|numeric',
            'price_3_option' => 'nullable|string|in:markup,price',
            'price_3_tiers'  => 'nullable|array',

            // --- Multi product_units Structure (Refactored to Flat) ---
            'product_units' => ['nullable', 'array'],

            // Validasi Data Dasar Unit
            'product_units.*.id'                => ['nullable', 'integer'],
            'product_units.*.name'              => ['required_with:product_units', 'string', 'max:255'],
            'product_units.*.conversion_factor' => ['required_with:product_units', 'numeric', 'min:0'],
            'product_units.*.barcode'           => ['nullable', 'string', 'max:255'],

            // Validasi Harga Flat di dalam Unit (Bukan nested 'prices' lagi)
            // Level 1
            'product_units.*.price_1'        => ['nullable', 'numeric'],
            'product_units.*.price_1_markup' => ['nullable', 'numeric'],
            'product_units.*.price_1_tiers'  => ['nullable', 'array'],

            // Level 2
            'product_units.*.price_2'        => ['nullable', 'numeric'],
            'product_units.*.price_2_markup' => ['nullable', 'numeric'],
            'product_units.*.price_2_tiers'  => ['nullable', 'array'],

            // Level 3
            'product_units.*.price_3'        => ['nullable', 'numeric'],
            'product_units.*.price_3_markup' => ['nullable', 'numeric'],
            'product_units.*.price_3_tiers'  => ['nullable', 'array'],
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
            'brand_id.exists'    => 'Brand yang dipilih tidak ditemukan.',
            'name.unique'        => 'Nama produk sudah digunakan.',

            'product_units.*.name.required_with' => 'Nama satuan tambahan harus diisi.',
            'product_units.*.conversion_factor.required_with' => 'Nilai konversi satuan harus diisi.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'id'            => $this->id ?? null,
            'description'   => $this->description ?? '',
            'barcode'       => $this->barcode ?? '',
            'notes'         => $this->notes ?? '',
            'uom'           => $this->uom ?? '',
            // Ensure arrays are present
            'price_1_tiers' => $this->price_1_tiers ?? [],
            'price_2_tiers' => $this->price_2_tiers ?? [],
            'price_3_tiers' => $this->price_3_tiers ?? [],
            'product_units' => $this->product_units ?? [],
        ]);
    }
}
