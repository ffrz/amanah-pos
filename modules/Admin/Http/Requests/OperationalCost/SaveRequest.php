<?php

namespace Modules\Admin\Http\Requests\OperationalCost;

use Illuminate\Foundation\Http\FormRequest;

/**
 * File ini menangani validasi input untuk Biaya Operasional.
 * Ia memastikan bahwa data yang masuk memenuhi aturan yang diperlukan
 * sebelum diteruskan ke layanan atau model.
 */
class SaveRequest extends FormRequest
{
    /**
     * Dapatkan aturan validasi yang berlaku untuk permintaan.
     * Aturan unik di sini menggunakan ID dari permintaan untuk mengabaikan
     * item saat pembaruan.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
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
            'notes'   => $this->input('notes') ?? '',
        ]);
    }
}
