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

namespace Modules\Admin\Http\Requests\User;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveRequest extends FormRequest
{
    /**
     * Dapatkan aturan validasi yang berlaku untuk request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $userId = $this->input('id');
        $isNew = empty($userId);
        $passwordProvided = !empty($this->input('password'));

        $rules = [
            'id' => 'nullable|integer|exists:users,id',
            'username' => [
                'required',
                'alpha_num',
                'min:3',
                'max:100',
                Rule::unique('users', 'username')->ignore($userId),
            ],
            'name'    => 'required|string|max:100',
            'type'    => ['required', Rule::in(array_keys(User::Types))],
            'roles'   => 'sometimes|array',
            'roles.*' => 'sometimes|integer|exists:acl_roles,id',
            'active'  => 'required|boolean',
        ];

        // Aturan kondisional untuk password
        if ($isNew || $passwordProvided) {
            $rules['password'] = 'required|string|min:5|max:40';
        }

        return $rules;
    }

    /**
     * Dapatkan pesan kesalahan yang disesuaikan.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'username.unique' => 'Username ini sudah digunakan.',
            'roles.*.exists' => 'Salah satu Role yang dipilih tidak valid.',
            'password.required' => 'Kata sandi wajib diisi saat membuat pengguna atau saat mengubah password.',
            'password.min' => 'Kata sandi minimal harus :min karakter.',
            'name.min' => 'Nama minimal harus :min karakter.',
            'name.max' => 'Nama maksimal :max karakter.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'id' => $this->id ?? null,
            'password' => $this->password ?? null,
            'roles' => $this->roles ?? [],
        ]);
    }
}
