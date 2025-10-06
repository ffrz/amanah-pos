<?php

namespace Modules\Admin\Http\Requests\FinanceTransaction;

use App\Models\FinanceTransaction;
use Illuminate\Foundation\Http\FormRequest;


class SaveRequest extends FormRequest
{

    public function rules(): array
    {
        return [
            'id'            => 'nullable|integer|exists:finance_transactions,id',
            'account_id'    => 'required|exists:finance_accounts,id',
            'to_account_id' => 'nullable|exists:finance_accounts,id|different:account_id',
            'datetime'      => 'required|date',
            'type'          => 'required|in:' . implode(',', array_keys(FinanceTransaction::Types)),
            'amount'        => 'required|numeric|min:0.01',
            'notes'         => 'nullable|string|max:255',
            'image_path'    => 'nullable|string',
            'image'         => 'nullable|image|max:15120',
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
