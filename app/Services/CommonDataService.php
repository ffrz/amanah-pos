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
use App\Models\FinanceAccount;
use App\Models\OperationalCostCategory;
use App\Models\ProductCategory;
use App\Models\Supplier;

class CommonDataService
{
    /**
     * Get product categories for dropdowns.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getProductCategories($fields = ['id', 'name'])
    {
        $query = ProductCategory::query()
            ->orderBy('name');

        return $query->get($fields);
    }

    /**
     * Get suppliers for dropdowns.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSuppliers($fields = ['id', 'name', 'phone'], $activeOnly = true)
    {
        $query = Supplier::query();

        if ($activeOnly) {
            $query->where('active', true);
        }

        $query->orderBy('name');

        return $query->get($fields);
    }

    /**
     * Get customers for dropdowns.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getCustomers($fields = ['id', 'code', 'name'], $activeOnly = true)
    {
        $query = Customer::query();

        if ($activeOnly) {
            $query->where('active', true);
        }

        $query->orderBy('code');

        return $query->get($fields);
    }

    /**
     * Get finance accounts for dropdowns.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getFinanceAccounts($fields = ['id', 'name', 'type', 'bank', 'number', 'holder', 'balance'], $activeOnly = true)
    {
        $query = FinanceAccount::query();

        if ($activeOnly) {
            $query->where('active', true);
        }

        $query->orderBy('name');

        return $query->get($fields);
    }

    public function getOperationalCategories($fields = ['id', 'name'])
    {
        $query = OperationalCostCategory::query()
            ->orderBy('name');

        return $query->get($fields);
    }
}
