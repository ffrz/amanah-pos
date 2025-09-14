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

namespace App\Services;

use App\Models\Customer;
use App\Models\ProductCategory;
use App\Models\Supplier;

class CommonDataService
{
    /**
     * Get product categories for dropdowns.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getCategories($fields = ['id', 'name'])
    {
        return ProductCategory::all($fields);
    }

    /**
     * Get suppliers for dropdowns.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSuppliers($fields = ['id', 'name', 'phone'])
    {
        return Supplier::all($fields);
    }

    /**
     * Get customers for dropdowns.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getCustomers($fields = ['id', 'username', 'name'])
    {
        return Customer::all($fields);
    }
}
