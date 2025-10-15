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

namespace Modules\Admin\Http\Requests\OperationalCost;

use App\Models\OperationalCost;
use Modules\Admin\Http\Requests\DefaultGetDataRequest;

/**
 * Validates request parameters for retrieving paginated and filtered
 * operational cost categories.
 */
class GetDataRequest extends DefaultGetDataRequest
{

    public function rules(): array
    {
        $rules = parent::rules();

        return array_merge($rules, [
            'order_by'  => 'nullable|string|in:id,code,date,finance_account_id,category_id,description,amount',
        ]);
    }
}
