<?php

namespace Modules\Admin\Http\Requests\ProductCategory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * File ini menangani validasi input untuk Operasi Kategori Biaya Operasional.
 * Ia memastikan bahwa data yang masuk memenuhi aturan yang diperlukan
 * sebelum diteruskan ke layanan atau model.
 */
class SaveRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna diizinkan untuk membuat permintaan ini.
     * Karena otorisasi akan ditangani di level controller, kita kembalikan true.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Dapatkan aturan validasi yang berlaku untuk permintaan.
     * Aturan unik di sini menggunakan ID dari permintaan untuk mengabaikan
     * item saat pembaruan.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        // Mendapatkan ID dari permintaan untuk kasus pembaruan.
        $itemId = $this->route('id') ?? $this->id;

        return [
            'name' => [
                'required',
                'max:255',
                Rule::unique('product_categories', 'name')->ignore($itemId),
            ],
            'description' => 'nullable|max:200',
        ];
    }
}
