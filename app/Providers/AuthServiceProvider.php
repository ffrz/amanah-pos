<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

use App\Models\OperationalCost;
use App\Models\OperationalCostCategory;
use App\Models\ProductCategory;
use Modules\Admin\Policies\OperationalCostCategoryPolicy;
use Modules\Admin\Policies\OperationalCostPolicy;
use Modules\Admin\Policies\ProductCategoryPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        ProductCategory::class => ProductCategoryPolicy::class,
        OperationalCostCategory::class => OperationalCostCategoryPolicy::class,
        OperationalCost::class => OperationalCostPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
