<?php

namespace Modules\Admin\Http\Requests\User;

use Illuminate\Validation\Rule;
use Modules\Admin\Http\Requests\DefaultGetDataRequest;

class GetDataRequest extends DefaultGetDataRequest
{
    /**
     * Dapatkan aturan validasi yang berlaku untuk request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {

        $rules = parent::rules();

        return array_merge($rules, [
            'order_by'    => ['nullable', 'string', Rule::in(['id', 'username', 'name'])],
            'filter.search' => ['nullable', 'string', 'max:100'],
        ]);
    }
}
