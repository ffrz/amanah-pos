<?php

namespace Modules\Admin\Http\Requests\Settings\CompanyProfile;

use Illuminate\Foundation\Http\FormRequest;

class SaveRequest extends FormRequest
{

    /**
     * Dapatkan aturan validasi yang berlaku untuk permintaan.
     */
    public function rules(): array
    {
        if ($this->isMethod('GET')) {
            return [];
        }

        return [
            'name'       => 'required|string|min:2|max:100',
            'headline'   => 'nullable|string|max:200',
            'phone'      => 'nullable|string|regex:/^(\+?\d{1,4})?[\s.-]?\(?\d{1,4}\)?[\s.-]?\d{1,4}[\s.-]?\d{1,9}$/|max:40',
            'address'    => 'nullable|string|max:500',
            'logo_path'  => 'nullable|max:500',
            'logo_image' => 'nullable|image|max:5120',
        ];
    }
}
