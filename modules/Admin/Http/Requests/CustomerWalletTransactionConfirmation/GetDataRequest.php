<?php

namespace Modules\Admin\Http\Requests\CustomerWalletTransactionConfirmation;

use Modules\Admin\Http\Requests\DefaultGetDataRequest;

class GetDataRequest extends DefaultGetDataRequest
{

    /**
     * Dapatkan aturan validasi yang berlaku untuk permintaan.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $rules = parent::rules();

        return array_merge($rules, [
            'order_by'  => 'nullable|string|in:id,datetime',
        ]);
    }
}
