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
use Illuminate\Validation\Rule;
use Modules\Service\Http\Requests\DefaultGetDataRequest;

class GetDataRequest extends DefaultGetDataRequest
{
    // /**
    //  * Dapatkan aturan validasi yang berlaku untuk request.
    //  *
    //  * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
    //  */
    // public function rules(): array
    // {
    //     return [
    //         'filter.search'         => 'nullable|string',
    //         'filter.order_status'   => ['nullable', Rule::in(array_keys(ServiceOrder::OrderStatuses))],
    //         'filter.service_status' => ['nullable', Rule::in(array_keys(ServiceOrder::ServiceStatuses))],
    //         'filter.payment_status' => ['nullable', Rule::in(array_keys(ServiceOrder::PaymentStatuses))],
    //         'filter.repair_status'  => ['nullable', Rule::in(array_keys(ServiceOrder::RepairStatuses))],
    //         'per_page'              => 'nullable|integer|min:1|max:100',
    //         'order_by'              => 'nullable|string|in:id,order_code,customer_name,received_datetime,total_cost',
    //         'order_type'            => 'nullable|string|in:asc,desc',
    //     ];
    // }
}
