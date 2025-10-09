<?php

namespace Modules\Admin\Http\Requests\CustomerWalletTransactionConfirmation;

use Illuminate\Foundation\Http\FormRequest;

class SaveRequest extends FormRequest
{
    /**
     * Dapatkan aturan validasi yang berlaku untuk permintaan.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules()
    {
        $id = $this->route('id') ?? $this->input('id');

        return [
            'id' => 'nullable|integer|exists:finance_accounts,id',
            'action' => 'required|string|in:accept,reject'
        ];
    }

    /**
     * Siapkan data untuk validasi.
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'id' => $this->id ?? null,
        ]);
    }
}
