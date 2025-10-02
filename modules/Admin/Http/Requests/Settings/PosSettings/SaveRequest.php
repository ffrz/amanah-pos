<?php

namespace Modules\Admin\Http\Requests\Settings\PosSettings;

use Illuminate\Foundation\Http\FormRequest;

class SaveRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna diizinkan untuk membuat request ini.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Sesuaikan otorisasi jika diperlukan, saat ini diizinkan untuk semua
        return true;
    }

    /**
     * Dapatkan aturan validasi yang berlaku untuk request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        if ($this->isMethod('GET')) {
            return [];
        }

        return [
            'default_payment_mode' => ['required', 'string'],
            'default_print_size' => ['required', 'string'],
            'after_payment_action' => ['required', 'string'],
            'foot_note' => ['nullable', 'string', 'max:200'],
        ];
    }
}
