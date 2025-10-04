<?php

namespace Modules\Admin\Http\Requests\UserActivityLog;

use App\Models\User;
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
            'filter.type' => ['nullable', 'string', Rule::in(['all', ...array_keys(User::Types)])],
            'filter.status' => ['nullable', 'string', Rule::in(['all', 'active', 'inactive'])],
            'filter.user_id' => ['nullable', 'string'],
            'filter.activity_name' => ['nullable', 'string'],
            'filter.activity_category' => ['nullable', 'string'],
            'filter.roles' => ['nullable', 'array'],
            'filter.search' => ['nullable', 'string', 'max:100'],
        ]);
    }
}
