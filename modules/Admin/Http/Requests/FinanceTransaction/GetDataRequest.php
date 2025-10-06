<?php

namespace Modules\Admin\Http\Requests\FinanceTransaction;

use Modules\Admin\Http\Requests\DefaultGetDataRequest;

class GetDataRequest extends DefaultGetDataRequest
{

    public function rules(): array
    {
        $rules = parent::rules();

        return array_merge($rules, [
            'order_by'  => 'nullable|string|in:id,date,description,amount',
        ]);
    }
}
