<?php

namespace Modules\Admin\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GetDataRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna diizinkan untuk membuat request ini.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Pengguna harus sudah login dan memiliki izin untuk melihat daftar pengguna (asumsi middleware sudah menangani izin ini)
        return true;
    }

    /**
     * Dapatkan aturan validasi yang berlaku untuk request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'order_by' => ['nullable', 'string', Rule::in(['name', 'username', 'active', 'created_at'])],
            'order_type' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'filter' => ['nullable', 'array'],
            'filter.role' => ['nullable', 'string'],
            'filter.status' => ['nullable', 'string', Rule::in(['active', 'inactive'])],
            'filter.search' => ['nullable', 'string', 'max:100'],
        ];
    }
}
