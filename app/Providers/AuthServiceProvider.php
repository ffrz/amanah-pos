<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

use App\Models\OperationalCost;
use App\Models\OperationalCostCategory;
use App\Models\ProductCategory;
use App\Models\User;
use Modules\Admin\Policies\OperationalCostCategoryPolicy;
use Modules\Admin\Policies\OperationalCostPolicy;
use Modules\Admin\Policies\ProductCategoryPolicy;
use Modules\Admin\Policies\UserPolicy;

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
        User::class => UserPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
