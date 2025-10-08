<?php

namespace Modules\Admin\Http\Requests\StockAdjustment;

use App\Models\StockAdjustment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * File ini menangani validasi input untuk Operasi Kategori Biaya Operasional.
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
        $itemId = $this->route('id') ?? $this->id;

        return [
            'id' => 'required|integer|exists:stock_adjustments,id',
            'datetime' => ['required', 'date'], // atau gunakan: 'date_format:Y-m-d H:i:s'
            'type' => [
                'required',
                Rule::in(array_keys(StockAdjustment::Types)),
            ],
            'notes' => 'nullable|string|max:1000',
            'details' => 'required|array',
            'action' => 'nullable|string|in:close,cancel,save'
        ];
    }

    protected function prepareForValidation(): void
    {
        $action_status_map = [
            'save' => StockAdjustment::Status_Draft,
            'close' => StockAdjustment::Status_Closed,
            'cancel' => StockAdjustment::Status_Cancelled,
        ];

        $this->merge([
            'status' => $action_status_map[$this->action],
        ]);
    }
}
