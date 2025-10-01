<?php

namespace Modules\Admin\Http\Requests\UserRole;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * File ini menangani validasi input untuk Peran Pengguna.
 * Ia memastikan bahwa data yang masuk memenuhi aturan yang diperlukan
 * sebelum diteruskan ke layanan atau model.
 */
class SaveRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna diizinkan membuat permintaan ini.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Dapatkan aturan validasi yang berlaku untuk permintaan.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'id' => 'nullable|integer|exists:acl_roles,id',
            'name' => [
                'required',
                'max:40',
                Rule::unique('acl_roles', 'name')->ignore($this->id),
            ],
            'description' => 'nullable|string|max:200',
            'permissions' => 'nullable|array',

            // Validasi: memastikan setiap item di array permissions ada di tabel acl_permissions
            // Ini berfungsi baik untuk array of objects (permissions.*.id) atau array of IDs (permissions.*)
            'permissions.*.id' => 'sometimes|required|exists:acl_permissions,id',
            'permissions.*' => 'sometimes|required|integer|exists:acl_permissions,id',
        ];
    }

    /**
     * Siapkan data untuk validasi.
     * Memindahkan logika pembersihan permissions dari controller ke sini.
     * Ini memastikan bahwa data yang diproses di Service sudah bersih.
     */
    protected function prepareForValidation(): void
    {
        $permissions = collect($this->permissions)->map(function ($permission) {
            // Jika permission adalah objek (dari Inertia/Vue), kita ambil hanya ID-nya
            return is_array($permission) && isset($permission['id']) ? $permission['id'] : $permission;
        })->filter()->values()->toArray(); // Filter null/empty dan reset keys

        $this->merge([
            // Memastikan permissions berisi array of IDs (integer)
            'permissions' => $permissions,
        ]);
    }
}
