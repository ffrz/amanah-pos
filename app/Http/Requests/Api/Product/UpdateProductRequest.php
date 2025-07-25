<?php

namespace App\Http\Requests\Api\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Hanya user yang login yang diizinkan memperbarui produk
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Ambil ID produk dari route parameter.
        // Asumsi nama parameter route adalah 'product' (misal: /api/products/{product})
        $productId = $this->route('product')->id ?? null;

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
                // 'name' harus unik, tetapi abaikan jika itu adalah nama produk yang sedang di-update
                Rule::unique('products', 'name')->ignore($productId),
            ],
            'description' => 'nullable|string|max:1000',
            'barcode' => 'nullable|string|max:255',
            'kode_barang' => 'nullable|string|max:255|unique:products,kode_barang,' . $productId, // Abaikan kode_barang milik produk yang sedang di-update
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
