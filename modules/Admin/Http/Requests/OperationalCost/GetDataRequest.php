<?php

namespace Modules\Admin\Http\Requests\OperationalCost;

use App\Models\OperationalCost;
use Modules\Admin\Http\Requests\DefaultGetDataRequest;

/**
 * Validates request parameters for retrieving paginated and filtered
 * operational cost categories.
 */
class GetDataRequest extends DefaultGetDataRequest
{

    public function rules(): array
    {
        return [
            'order_by'  => 'nullable|string|in:id,date,description,amount',
            'order_type'  => 'nullable|string|in:asc,desc',
            'per_page'  => 'nullable|integer|min:1|max:100',
            'filter.search' => 'nullable|string|max:100',
        ];
    }
}
