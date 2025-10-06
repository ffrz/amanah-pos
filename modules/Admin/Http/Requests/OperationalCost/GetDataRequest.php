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
        $rules = parent::rules();

        return array_merge($rules, []);
    }
}
