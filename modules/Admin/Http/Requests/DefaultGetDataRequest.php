<?php

namespace Modules\Admin\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates request parameters for retrieving paginated and filtered
 * operational cost categories.
 */
class DefaultGetDataRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'order_by' => 'nullable|string',
            'order_type' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', Rule::in([10, 25, 50, 100])],
            'filter' => ['nullable', 'array'],
            'filter.*' => ['nullable'],
        ];
    }

    /**
     * Prepare data for validation, including the default values.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'order_by'   => $this->order_by ?? 'id',
            'order_type' => $this->order_type ?? 'desc',
            'per_page'   => $this->per_page ?? 10,
            'filter'     => $this->filter ?? [],
        ]);
    }
}
