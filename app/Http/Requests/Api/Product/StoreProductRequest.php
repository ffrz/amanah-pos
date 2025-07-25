<?php

namespace App\Http\Requests\Api\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Hanya user yang login yang diizinkan membuat produk
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
            'category_id' => [
                'nullable',
                Rule::exists('product_categories', 'id'),
            ],
            'supplier_id' => [
                'nullable',
                Rule::exists('suppliers', 'id'),
            ],
            'type' => [
                'nullable',
                Rule::in(['stocked', 'non_stocked']), // Sesuaikan dengan konstanta Product::Types Anda
            ],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products', 'name'), // 'name' harus unik saat membuat produk baru
            ],
            'description' => 'nullable|string|max:1000',
            'barcode' => 'nullable|string|max:255',
            'kode_barang' => 'nullable|string|max:255|unique:products,kode_barang', // Assuming this is also unique
            'uom' => 'nullable|string|max:255',
            'stock' => 'nullable|numeric|min:0',
            'min_stock' => 'nullable|numeric|min:0',
            'max_stock' => 'nullable|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'price' => 'nullable|numeric|min:0',
            'active' => 'nullable|boolean',
            'notes' => 'nullable|string|max:1000',
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
            'category_id.exists' => 'Kategori yang dipilih tidak valid.',
            'supplier_id.exists' => 'Supplier yang dipilih tidak valid.',
            'name.unique' => 'Nama produk sudah digunakan.',
            'kode_barang.unique' => 'Kode barang sudah digunakan.',
            // Anda bisa tambahkan pesan kustom lainnya di sini
        ];
    }
}
