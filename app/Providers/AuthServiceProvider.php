<?php

namespace App\Providers;

use App\Models\CashierSession;
use App\Models\CashierTerminal;
use App\Models\Customer;
use App\Models\FinanceAccount;
use App\Models\FinanceTransaction;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

use App\Models\OperationalCost;
use App\Models\OperationalCostCategory;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderPayment;
use App\Models\SalesOrder;
use App\Models\SalesOrderPayment;
use App\Models\StockAdjustment;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use Modules\Admin\Policies\DefaultPolicy;
use Modules\Admin\Policies\OnlyAuthorPolicy;
use Modules\Admin\Policies\OperationalCostCategoryPolicy;
use Modules\Admin\Policies\OperationalCostPolicy;
use Modules\Admin\Policies\ProductCategoryPolicy;
use Modules\Admin\Policies\UserPolicy;
use Spatie\Permission\Models\Role;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class => UserPolicy::class,
        Role::class => DefaultPolicy::class,
        ProductCategory::class => ProductCategoryPolicy::class,
        OperationalCostCategory::class => OperationalCostCategoryPolicy::class,
        OperationalCost::class => OperationalCostPolicy::class,
        CashierSession::class => OnlyAuthorPolicy::class,
        CashierTerminal::class => DefaultPolicy::class,
        Product::class => DefaultPolicy::class,
        Supplier::class => DefaultPolicy::class,
        Customer::class => DefaultPolicy::class,
        FinanceAccount::class => DefaultPolicy::class,
        FinanceTransaction::class => DefaultPolicy::class,
        StockAdjustment::class => DefaultPolicy::class,
        StockMovement::class => DefaultPolicy::class,
        SalesOrder::class => DefaultPolicy::class,
        SalesOrderPayment::class => DefaultPolicy::class,
        PurchaseOrder::class => DefaultPolicy::class,
        PurchaseOrderPayment::class => DefaultPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
