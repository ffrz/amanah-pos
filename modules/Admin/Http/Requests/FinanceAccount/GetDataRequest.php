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
        return [
            'order_by'  => 'nullable|string|in:name,type,balance',
            'order_type'  => 'nullable|string|in:asc,desc',
            'per_page'  => 'nullable|integer|min:1|max:100',
            'filter.search' => 'nullable|string|max:100',
            'filter.status' => 'nullable|string|in:all,active,inactive',
            'filter.type' => 'nullable|string|in:' . join(',', ['all', ...array_keys(FinanceAccount::Types)]),
        ];
    }

}
