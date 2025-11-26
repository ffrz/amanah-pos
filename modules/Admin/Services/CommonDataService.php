<?php

/**
 * Proprietary Software / Perangkat Lunak Proprietary
 * Copyright (c) 2025 Fahmi Fauzi Rahman. All rights reserved.
 * * EN: Unauthorized use, copying, modification, or distribution is prohibited.
 * ID: Penggunaan, penyalinan, modifikasi, atau distribusi tanpa izin dilarang.
 * * See the LICENSE file in the project root for full license information.
 * Lihat file LICENSE di root proyek untuk informasi lisensi lengkap.
 * * GitHub: https://github.com/ffrz
 * Email: fahmifauzirahman@gmail.com
 */

namespace Modules\Admin\Services;

use App\Models\Customer;
use App\Models\FinanceAccount;
use App\Models\FinanceTransactionCategory;
use App\Models\FinanceTransactionTag;
use App\Models\OperationalCostCategory;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Uom;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CommonDataService
{
    /**
     * Injeksi dependensi Repository
     * @param ProductCategoryRepositoryInterface $productCategoryRepository
     */
    public function __construct()
    {
        // Dependencies untuk service lain (Supplier, Customer, dll.)
        // harus diinjeksikan di sini juga jika metode mereka direfaktor.
    }

    /**
     * Get product categories (Cached).
     *
     * @return Collection
     */
    public function getProductCategories(): Collection
    {
        return app(ProductCategoryService::class)->getAllProductCategories();
    }

    /**
     * Get suppliers.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSuppliers($fields = ['id', 'code', 'name', 'phone_1'], $activeOnly = true)
    {
        // TODO: Refactor ini dengan SupplierRepositoryInterface dan implementasi caching.
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
        // TODO: Refactor ini dengan CustomerRepositoryInterface dan implementasi caching.
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
        // TODO: Refactor ini dengan FinanceAccountRepositoryInterface dan implementasi caching.
        $query = FinanceAccount::query();

        if ($activeOnly) {
            $query->where('active', true);
        }

        $query->orderBy('name');

        return $query->get($fields);
    }

    /**
     * Get finance transaction categories.
     */
    public function getFinanceTransactionCategories($fields = ['id', 'name'])
    {
        $query = FinanceTransactionCategory::query();

        $query->orderBy('name');

        return $query->get($fields);
    }

    /**
     * Get finance transaction tags.
     */
    public function getFinanceTransactionTags($fields = ['id', 'name'])
    {
        $query = FinanceTransactionTag::query();

        $query->orderBy('name');

        return $query->get($fields);
    }


    /**
     * Get operational cost categories.
     */
    public function getOperationalCategories($fields = ['id', 'name'])
    {
        // TODO: Refactor ini dengan OperationalCostCategoryRepositoryInterface dan implementasi caching.
        $query = OperationalCostCategory::query()
            ->orderBy('name');

        return $query->get($fields);
    }

    public function getAclPermissions()
    {
        // TODO: Pertimbangkan caching di sini.
        $permissions = Permission::all();
        return $permissions;
    }

    public function getRoles()
    {
        // TODO: Pertimbangkan caching di sini.
        return Role::orderBy('name', 'asc')->get();
    }

    public function getAllUsers($cols = ['*'])
    {
        // TODO: Pertimbangkan caching di sini.
        return User::query()->select($cols)->orderBy('name', 'asc')->get();
    }

    public function getProducts($fields = ['id', 'name', 'description'], $activeOnly = true)
    {
        // TODO: Refactor ini dengan ProductRepositoryInterface dan implementasi caching.
        $query = Product::query();

        if ($activeOnly) {
            $query->where('active', true);
        }

        $query->orderBy('name');

        return $query->get($fields);
    }

    public function getUoms()
    {
        $query = Uom::query();
        $query->orderBy('name');
        return $query->get();
    }
}
