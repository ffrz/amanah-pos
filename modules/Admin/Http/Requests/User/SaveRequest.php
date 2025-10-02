<?php

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
            'id'       => ['nullable', 'integer', 'exists:users,id'],
            'username' => [
                'required',
                'alpha_num',
                'max:255',
                Rule::unique('users', 'username')->ignore($userId),
            ],
            'name'     => 'required|string|max:255',
            'type'       => ['required', Rule::in(array_keys(User::Types))],
            'roles'      => 'sometimes|array',
            'roles.*' => 'sometimes|integer|exists:acl_roles,id',
            'active'     => 'required|boolean',
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
            'password.required' => 'Password wajib diisi saat membuat pengguna atau saat mengubah password.',
            'password.min' => 'Password minimal harus :min karakter.',
        ];
    }
}
