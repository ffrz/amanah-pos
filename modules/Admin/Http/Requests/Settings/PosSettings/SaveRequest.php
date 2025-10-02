<?php

namespace Modules\Admin\Http\Requests\Settings\PosSettings;

use Illuminate\Foundation\Http\FormRequest;

class SaveRequest extends FormRequest
{
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
