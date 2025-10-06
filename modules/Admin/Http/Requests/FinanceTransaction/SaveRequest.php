<?php

namespace Modules\Admin\Http\Requests\FinanceTransaction;

use Illuminate\Foundation\Http\FormRequest;


class SaveRequest extends FormRequest
{

    public function rules(): array
    {
        return [
            'id'                 => 'nullable|integer|exists:operational_costs,id',
            'finance_account_id' => 'nullable|exists:finance_accounts,id',
            'date'               => 'required|date',
            'category_id'        => 'nullable',
            'description'        => 'required|max:255',
            'amount'             => 'required|numeric|gt:0',
            'notes'              => 'nullable|max:200',
            'image_path'         => 'nullable|string',
            'image'              => 'nullable|image|max:15120',
        ];
    }


    /**
     * Prepare data for validation, including the default values.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'id'      => $this->id ?? null,
            'notes'   => $this->notes ?? '',
        ]);
    }
}
