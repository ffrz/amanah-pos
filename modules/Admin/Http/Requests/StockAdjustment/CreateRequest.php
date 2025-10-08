<?php

namespace Modules\Admin\Http\Requests\StockAdjustment;

use App\Models\StockAdjustment;
use Illuminate\Foundation\Http\FormRequest;


class CreateRequest extends FormRequest
{

    public function rules(): array
    {
        if ($this->isMethod('GET')) return [];

        return [
            'product_ids' => 'required|array',
            'datetime' => 'required|date',
            'type' => 'required|string|in:' . join(",", array_keys(StockAdjustment::Types)),
            'notes' => 'nullable|string|max:200',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'product_ids' => $this->product_ids ?? [],
            'datetime' => $this->datetime ?? date('Y-m-d H:i:s'),
            'type' => $this->type ?? StockAdjustment::Type_StockCorrection,
            'notes' => $this->notes ?? '',
        ]);
    }

    public function messages(): array
    {
        return [
            'product_ids.required' => 'Anda belum memilih produk!',
        ];
    }
}
