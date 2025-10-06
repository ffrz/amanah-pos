<?php

namespace Modules\Admin\Http\Requests\Customer;

use App\Models\Customer;
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
        $id = $this->input('id');

        $rules = [
            'id' => 'nullable|integer|exists:customers,id',
            'code' => 'required|string|max:40|unique:customers,code' . ($id ? ',' . $id : ''),
            'type' => [
                'required',
                'max:40',
                Rule::in(array_keys(Customer::Types)),
            ],
            'name' => 'required|max:255',
            'phone' => 'nullable|max:100',
            'address' => 'nullable|max:1000',
            'active'   => 'required|boolean',
            'password' => (!$id ? 'required|' : '') . 'min:5|max:40',
        ];

        return $rules;
    }

    /**
     * Dapatkan pesan kesalahan yang disesuaikan.
     *
     * @return array
     */
    public function messages(): array
    {
        return [];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'id' => $this->id ?? null,
            'phone' => $this->phone ?? '',
            'address' => $this->address ?? '',
        ]);
    }
}
