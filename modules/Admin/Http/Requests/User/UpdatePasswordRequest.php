<?php

namespace Modules\Admin\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdatePasswordRequest extends FormRequest
{
    /**
     * Dapatkan aturan validasi yang berlaku untuk request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            // Current password hanya di-cek keberadaannya di sini. Pengecekan ke database dilakukan di Service.
            'current_password' => ['required', 'string'],
            // Password baru
            'password' => 'required|string|confirmed|min:5',
        ];
    }

    /**
     * Dapatkan pesan kesalahan yang disesuaikan untuk aturan validasi tertentu.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password minimal harus terdiri dari :min karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok dengan password baru.',
        ];
    }
}
