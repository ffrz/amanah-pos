<?php

/**
 * Proprietary Software / Perangkat Lunak Proprietary
 * Copyright (c) 2025 Fahmi Fauzi Rahman. All rights reserved.
 *
 * EN: Unauthorized use, copying, modification, or distribution is prohibited.
 * ID: Penggunaan, penyalinan, modifikasi, atau distribusi tanpa izin dilarang.
 *
 * See the LICENSE file in the project root for full license information.
 * Lihat file LICENSE di root proyek untuk informasi lisensi lengkap.
 *
 * GitHub: https://github.com/ffrz
 * Email: fahmifauzirahman@gmail.com
 */

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
            'category_id'   => 'nullable|exists:finance_transaction_categories,id',
            'tags'          => 'nullable|array|distinct',
            'tags.*'        => 'integer|exists:finance_transaction_tags,id',
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
            'id' => $this->id ?? null,
            'category_id' => $this->category_id ?? null,
            'tags' => $this->tags ?? [],
            'notes' => $this->notes ?? '',
        ]);
    }
}
