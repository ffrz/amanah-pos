<?php

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
        // Mendapatkan instance produk jika ini adalah request update (melalui route model binding)
        // Jika ini adalah request store, $product akan null
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
            'description' => ['nullable', 'string', 'max:1000'],
            'barcode'   => ['nullable', 'string', 'max:255'],
            'uom'       => ['nullable', 'string', 'max:255'],
            'stock'     => ['nullable', 'numeric'],
            'min_stock' => ['nullable', 'numeric'],
            'max_stock' => ['nullable', 'numeric'],
            'cost'      => ['nullable', 'numeric'],
            'price'     => ['nullable', 'numeric'],
            'price_2'   => ['nullable', 'numeric'],
            'price_3'   => ['nullable', 'numeric'],
            'active'    => ['nullable', 'boolean'],
            'price_editable' => ['nullable', 'boolean'],
            'notes'     => ['nullable', 'string', 'max:1000'],
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
