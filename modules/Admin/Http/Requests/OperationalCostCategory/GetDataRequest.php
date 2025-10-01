<?php

namespace Modules\Admin\Http\Requests\CashierTerminal;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates request parameters for retrieving paginated and filtered
 * operational cost categories.
 */
class GetDataRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'order_by' => [
                'nullable',
                'string',
                Rule::in(['id', 'name', 'description', 'created_at', 'updated_at']),
            ],
            'order_type' => ['nullable', 'string', Rule::in(['asc', 'desc'])],

            // Filters
            'filter' => [
                'nullable',
                'array',
            ],
            'filter.search' => ['nullable', 'string', 'max:255'],
        ];
    }
}
