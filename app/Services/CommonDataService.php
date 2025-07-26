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
    public function getCategories()
    {
        return ProductCategory::all(['id', 'name']);
    }

    /**
     * Get suppliers for dropdowns.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSuppliers()
    {
        return Supplier::all(['id', 'name', 'phone']);
    }

    /**
     * Get customers for dropdowns.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getCustomers()
    {
        return Customer::all(['id', 'nis', 'name']);
    }
}
