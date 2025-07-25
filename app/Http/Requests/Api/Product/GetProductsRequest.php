<?php

namespace App\Http\Requests\Api\Product;

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
        // Secara default, Form Request hanya bisa diotorisasi jika pengguna yang request sudah login.
        // Jika API ini hanya untuk pengguna terotentikasi (seperti yang kita diskusikan),
        // maka Auth::check() atau $this->user() !== null adalah cara yang tepat.
        // Jika Anda ingin mengizinkan akses publik (tanpa login) ke endpoint ini, ubah menjadi 'return true;'.
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
            // Pagination
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],

            // Sorting
            'order_by' => ['nullable', 'string', 'in:id,name,barcode,kode_barang,price,created_at'], // Kolom yang bisa diurutkan
            'order_type' => ['nullable', 'string', 'in:asc,desc'], // Arah pengurutan

            // General Search (opsional, jika beda dengan 'query')
            'search' => ['nullable', 'string', 'max:255'],

            // Live Search (mencari di berbagai kolom)
            'query' => ['nullable', 'string', 'max:255'],

            // Filters
            'category_id' => ['nullable', 'integer', 'exists:product_categories,id'], // Memastikan category_id ada di tabel product_categories
            'supplier_id' => ['nullable', 'integer', 'exists:suppliers,id'],     // Memastikan supplier_id ada di tabel suppliers
            'type' => ['nullable', 'string', 'in:stocked,non_stocked'], // Sesuaikan dengan nilai konstanta Product::Types Anda
            'stock_status' => ['nullable', 'string', 'in:low,out,over,ready'], // Filter status stok
            'status' => ['nullable', 'string', 'in:active,inactive'], // Filter status aktif produk
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
            'category_id.exists' => 'The selected category does not exist.',
            'supplier_id.exists' => 'The selected supplier does not exist.',
            'order_by.in' => 'The selected sort by option is invalid.',
            'order_type.in' => 'The selected order type is invalid.',
            // Anda bisa menambahkan pesan kustom lain di sini
        ];
    }
}
