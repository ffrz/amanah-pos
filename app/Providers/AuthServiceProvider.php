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

namespace App\Providers;

use App\Models\CashierCashDrop;
use App\Models\CashierSession;
use App\Models\CashierTerminal;
use App\Models\Customer;
use App\Models\CustomerLedger;
use App\Models\CustomerWalletTransaction;
use App\Models\CustomerWalletTransactionConfirmation;
use App\Models\FinanceAccount;
use App\Models\FinanceTransaction;
use App\Models\FinanceTransactionCategory;
use App\Models\FinanceTransactionTag;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

use App\Models\OperationalCost;
use App\Models\OperationalCostCategory;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderPayment;
use App\Models\PurchaseOrderReturn;
use App\Models\SalesOrder;
use App\Models\SalesOrderPayment;
use App\Models\SalesOrderReturn;
use App\Models\ServiceTechnician;
use App\Models\StockAdjustment;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\SupplierLedger;
use App\Models\SupplierWalletTransaction;
use App\Models\TaxScheme;
use App\Models\User;
use Modules\Admin\Policies\CashierCashDropPolicy;
use Modules\Admin\Policies\DefaultPolicy;
use Modules\Admin\Policies\OnlyAuthorPolicy;
use Modules\Admin\Policies\OperationalCostCategoryPolicy;
use Modules\Admin\Policies\OperationalCostPolicy;
use Modules\Admin\Policies\ProductCategoryPolicy;
use Modules\Admin\Policies\PurchaseOrderPolicy;
use Modules\Admin\Policies\PurchaseOrderReturnPolicy;
use Modules\Admin\Policies\SalesOrderPolicy;
use Modules\Admin\Policies\SalesOrderReturnPolicy;
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
        SalesOrder::class => SalesOrderPolicy::class,
        SalesOrderPayment::class => DefaultPolicy::class,
        SalesOrderReturn::class => SalesOrderReturnPolicy::class,
        PurchaseOrder::class => PurchaseOrderPolicy::class,
        PurchaseOrderPayment::class => DefaultPolicy::class,
        PurchaseOrderReturn::class => PurchaseOrderReturnPolicy::class,
        CustomerWalletTransactionConfirmation::class => DefaultPolicy::class,
        CustomerWalletTransaction::class => DefaultPolicy::class,
        SupplierWalletTransaction::class => DefaultPolicy::class,
        TaxScheme::class => DefaultPolicy::class,
        FinanceTransactionCategory::class => DefaultPolicy::class,
        FinanceTransactionTag::class => DefaultPolicy::class,
        CashierCashDrop::class => CashierCashDropPolicy::class,
        CustomerLedger::class => DefaultPolicy::class,
        SupplierLedger::class => DefaultPolicy::class,

        ServiceTechnician::class => DefaultPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
