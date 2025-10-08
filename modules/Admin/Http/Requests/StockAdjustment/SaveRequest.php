<?php

namespace Modules\Admin\Http\Requests\StockAdjustment;

use App\Models\StockAdjustment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;


class SaveRequest extends FormRequest
{

    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:stock_adjustments,id',
            'datetime' => ['required', 'date'], // atau gunakan: 'date_format:Y-m-d H:i:s'
            'type' => [
                'required',
                Rule::in(array_keys(StockAdjustment::Types)),
            ],
            'notes' => 'nullable|string|max:1000',
            'details' => 'required|array',
            'action' => 'nullable|string|in:close,cancel,save',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'notes' => $this->notes ?? '',
        ]);
    }
}
