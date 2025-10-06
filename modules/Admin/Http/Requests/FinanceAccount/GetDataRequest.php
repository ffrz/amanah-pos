<?php

namespace Modules\Admin\Http\Requests\FinanceAccount;

use App\Models\FinanceAccount;
use Modules\Admin\Http\Requests\DefaultGetDataRequest;

class GetDataRequest extends DefaultGetDataRequest
{

    /**
     * Dapatkan aturan validasi yang berlaku untuk permintaan.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $rules = parent::rules();

        return array_merge($rules, [
            'order_by'  => 'nullable|string|in:name,type,balance',
            'filter.search' => 'nullable|string|max:100',
            'filter.status' => 'nullable|string|in:all,active,inactive',
            'filter.type' => 'nullable|string|in:' . join(',', ['all', ...array_keys(FinanceAccount::Types)]),
        ]);
    }
}
