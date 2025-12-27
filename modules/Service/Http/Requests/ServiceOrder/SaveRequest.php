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

namespace Modules\Service\Http\Requests\ServiceOrder;

use App\Models\ServiceOrder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveRequest extends FormRequest
{
    /**
     * Dapatkan aturan validasi yang berlaku untuk request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'id'                 => 'nullable|integer|exists:service_orders,id',

            // Diubah: customer_id tidak lagi wajib exists di tabel customers
            // Ini memungkinkan pengiriman nilai 0 atau null untuk input on-the-fly
            'customer_id'        => 'nullable|integer',

            'customer_name'      => 'required|string|max:255',
            'customer_phone'     => 'required|string|max:100',
            'customer_address'   => 'nullable|string|max:1000',

            'device_type'        => 'required|string',
            'device'             => 'required|string|max:255',
            'equipments'         => 'required|string',
            'device_sn'          => 'nullable|string|max:255',

            'order_status'       => ['required', Rule::in(array_keys(ServiceOrder::OrderStatuses))],
            'service_status'     => ['required', Rule::in(array_keys(ServiceOrder::ServiceStatuses))],
            'payment_status'     => ['required', Rule::in(array_keys(ServiceOrder::PaymentStatuses))],
            'repair_status'      => ['required', Rule::in(array_keys(ServiceOrder::RepairStatuses))],

            'technician_id'      => 'nullable|integer|exists:service_technicians,id',

            'received_datetime'  => 'required|date',
            'checked_datetime'   => 'nullable|date',
            'worked_datetime'    => 'nullable|date',
            'completed_datetime' => 'nullable|date',
            'picked_datetime'    => 'nullable|date',

            'down_payment'       => 'nullable|numeric|min:0',
            'estimated_cost'     => 'nullable|numeric|min:0',
            'total_cost'         => 'nullable|numeric|min:0',

            'warranty_day_count' => 'nullable|integer|min:0',
            'notes'              => 'nullable|string',

            'problems'           => 'nullable|string',
            'actions'            => 'nullable|string',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'id' => $this->id ?? null,
            'device_sn' => $this->device_sn ?? '',
            'notes' => $this->notes ?? '',
            'problems' => $this->problems ?? '',
            'actions' => $this->actions ?? '',
            'down_payment' => $this->down_payment ?? 0,
            'estimated_cost' => $this->estimated_cost ?? 0,
            'total_cost' => $this->total_cost ?? 0,
        ]);
    }
}
