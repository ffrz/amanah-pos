<?php

namespace App\Providers;

use App\Models\OperationalCostCategory;
use App\Policies\OperationalCostCategoryPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // Mengarahkan model OperationalCostCategory ke Policy-nya
        OperationalCostCategory::class => OperationalCostCategoryPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
