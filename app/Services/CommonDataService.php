<?php

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
    public function getCustomers($fields = ['id', 'nis', 'name'])
    {
        return Customer::all($fields);
    }
}
