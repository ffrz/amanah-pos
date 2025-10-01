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

namespace Modules\Admin\Services;

use App\Models\Customer;
use App\Models\FinanceAccount;
use App\Models\OperationalCostCategory;
use App\Models\ProductCategory;
use App\Models\Supplier;

class CommonDataService
{
    /**
     * Get product categories.
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
     * Get suppliers.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSuppliers($fields = ['id', 'name', 'phone_1'], $activeOnly = true)
    {
        $query = Supplier::query();

        if ($activeOnly) {
            $query->where('active', true);
        }

        $query->orderBy('name');

        return $query->get($fields);
    }

    /**
     * Get customers.
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
     * Get finance accounts.
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

    /**
     * Get operational cost categories.
     */
    public function getOperationalCategories($fields = ['id', 'name'])
    {
        $query = OperationalCostCategory::query()
            ->orderBy('name');

        return $query->get($fields);
    }
}
