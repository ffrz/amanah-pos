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

use App\Http\Middleware\Auth;
use App\Http\Middleware\CheckAdminRoutePermission;
use App\Http\Middleware\NonAuthenticated;
use Illuminate\Support\Facades\Route;
use Modules\Admin\Http\Controllers\AuthController;
use Modules\Admin\Http\Controllers\CashierSessionController;
use Modules\Admin\Http\Controllers\CashierTerminalController;
use Modules\Admin\Http\Controllers\CustomerController;
use Modules\Admin\Http\Controllers\CustomerWalletTransactionConfirmationController;
use Modules\Admin\Http\Controllers\CustomerWalletTransactionController;
use Modules\Admin\Http\Controllers\DashboardController;
use Modules\Admin\Http\Controllers\DocumentVersionController;
use Modules\Admin\Http\Controllers\FinanceAccountController;
use Modules\Admin\Http\Controllers\FinanceTransactionController;
use Modules\Admin\Http\Controllers\OperationalCostController;
use Modules\Admin\Http\Controllers\OperationalCostCategoryController;
use Modules\Admin\Http\Controllers\ProductCategoryController;
use Modules\Admin\Http\Controllers\ProductController;
use Modules\Admin\Http\Controllers\PurchaseOrderController;
use Modules\Admin\Http\Controllers\PurchaseOrderReturnController;
use Modules\Admin\Http\Controllers\SalesOrderController;
use Modules\Admin\Http\Controllers\SalesOrderReturnController;
use Modules\Admin\Http\Controllers\StockAdjustmentController;
use Modules\Admin\Http\Controllers\StockMovementController;
use Modules\Admin\Http\Controllers\SupplierController;
use Modules\Admin\Http\Controllers\Settings\CompanyProfileController;
use Modules\Admin\Http\Controllers\Settings\UserProfileController;
use Modules\Admin\Http\Controllers\Settings\UserController;
use Modules\Admin\Http\Controllers\Settings\UserRoleController;
use Modules\Admin\Http\Controllers\Settings\DatabaseSettingsController;
use Modules\Admin\Http\Controllers\Settings\PosSettingsController;
use Modules\Admin\Http\Controllers\Settings\UserActivityLogController;

Route::middleware(NonAuthenticated::class)
    ->group(function () {
        Route::prefix('/auth')->group(function () {
            Route::match(['get', 'post'], 'login', [AuthController::class, 'login'])->name('admin.auth.login');
            Route::match(['get', 'post'], 'register', [AuthController::class, 'register'])->name('admin.auth.register');
            Route::match(['get', 'post'], 'forgot-password', [AuthController::class, 'forgotPassword'])->name('admin.auth.forgot-password');
        });
    });

Route::middleware([Auth::class])
    ->group(function () {
        Route::redirect('', 'admin/dashboard');
        Route::match(['get', 'post'], 'auth/logout', [AuthController::class, 'logout'])->name('admin.auth.logout');
        Route::get('dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
        Route::get('test', [DashboardController::class, 'test'])->name('admin.test');
        Route::get('about', function () {
            return inertia('About');
        })->name('admin.about');

        Route::prefix('settings')->group(function () {
            Route::get('profile/edit', [UserProfileController::class, 'edit'])->name('admin.user-profile.edit');
            Route::post('profile/update', [UserProfileController::class, 'update'])->name('admin.user-profile.update');
            Route::post('profile/update-password', [UserProfileController::class, 'updatePassword'])->name('admin.user-profile.update-password');
        });

        Route::middleware([CheckAdminRoutePermission::class])->group(function () {
            Route::prefix('products')->group(function () {
                Route::get('', [ProductController::class, 'index'])->name('admin.product.index');
                Route::get('data', [ProductController::class, 'data'])->name('admin.product.data');
                Route::get('add', [ProductController::class, 'editor'])->name('admin.product.add');
                Route::get('duplicate/{id}', [ProductController::class, 'duplicate'])->name('admin.product.duplicate');
                Route::get('edit/{id}', [ProductController::class, 'editor'])->name('admin.product.edit');
                Route::post('save', [ProductController::class, 'save'])->name('admin.product.save');
                Route::post('delete/{id}', [ProductController::class, 'delete'])->name('admin.product.delete');
                Route::get('detail/{id}', [ProductController::class, 'detail'])->name('admin.product.detail');
                Route::match(['get', 'post'], 'import', [ProductController::class, 'import'])->name('admin.product.import');
                Route::match(['get', 'post'], 'send-price-list', [ProductController::class, 'sendPriceList'])->name('admin.product.send-price-list');
            });

            Route::prefix('stock-adjustments')->group(function () {
                Route::get('', [StockAdjustmentController::class, 'index'])->name('admin.stock-adjustment.index');
                Route::get('data', [StockAdjustmentController::class, 'data'])->name('admin.stock-adjustment.data');
                Route::match(['get', 'post'], 'create', [StockAdjustmentController::class, 'create'])->name('admin.stock-adjustment.create');
                Route::get('editor/{id}', [StockAdjustmentController::class, 'editor'])->name('admin.stock-adjustment.editor');
                Route::post('save', [StockAdjustmentController::class, 'save'])->name('admin.stock-adjustment.save');
                Route::post('delete/{id}', [StockAdjustmentController::class, 'delete'])->name('admin.stock-adjustment.delete');
                Route::get('detail/{id}', [StockAdjustmentController::class, 'detail'])->name('admin.stock-adjustment.detail');
                Route::get('print-stock-card/{id}', [StockAdjustmentController::class, 'printStockCard'])->name('admin.stock-adjustment.print-stock-card');
                Route::get('print/{id}', [StockAdjustmentController::class, 'print'])->name('admin.stock-adjustment.print');
            });

            Route::prefix('stock-movements')->group(function () {
                Route::get('', [StockMovementController::class, 'index'])->name('admin.stock-movement.index');
                Route::get('data', [StockMovementController::class, 'data'])->name('admin.stock-movement.data');
            });

            Route::prefix('product-categories')->group(function () {
                Route::get('', [ProductCategoryController::class, 'index'])->name('admin.product-category.index');
                Route::get('data', [ProductCategoryController::class, 'data'])->name('admin.product-category.data');
                Route::get('add', [ProductCategoryController::class, 'editor'])->name('admin.product-category.add');
                Route::get('duplicate/{id}', [ProductCategoryController::class, 'duplicate'])->name('admin.product-category.duplicate');
                Route::get('edit/{id}', [ProductCategoryController::class, 'editor'])->name('admin.product-category.edit');
                Route::post('save', [ProductCategoryController::class, 'save'])->name('admin.product-category.save');
                Route::post('delete/{id}', [ProductCategoryController::class, 'delete'])->name('admin.product-category.delete');
            });

            Route::prefix('finance-accounts')->group(function () {
                Route::get('', [FinanceAccountController::class, 'index'])->name('admin.finance-account.index');
                Route::get('data', [FinanceAccountController::class, 'data'])->name('admin.finance-account.data');
                Route::get('add', [FinanceAccountController::class, 'editor'])->name('admin.finance-account.add');
                Route::get('duplicate/{id}', [FinanceAccountController::class, 'duplicate'])->name('admin.finance-account.duplicate');
                Route::get('edit/{id}', [FinanceAccountController::class, 'editor'])->name('admin.finance-account.edit');
                Route::get('detail/{id}', [FinanceAccountController::class, 'detail'])->name('admin.finance-account.detail');
                Route::post('save', [FinanceAccountController::class, 'save'])->name('admin.finance-account.save');
                Route::post('delete/{id}', [FinanceAccountController::class, 'delete'])->name('admin.finance-account.delete');
            });

            Route::prefix('finance-transactions')->group(function () {
                Route::get('', [FinanceTransactionController::class, 'index'])->name('admin.finance-transaction.index');
                Route::get('data', [FinanceTransactionController::class, 'data'])->name('admin.finance-transaction.data');
                Route::get('add', [FinanceTransactionController::class, 'editor'])->name('admin.finance-transaction.add');
                Route::get('edit/{id}', [FinanceTransactionController::class, 'editor'])->name('admin.finance-transaction.edit');
                Route::get('detail/{id}', [FinanceTransactionController::class, 'detail'])->name('admin.finance-transaction.detail');
                Route::post('save', [FinanceTransactionController::class, 'save'])->name('admin.finance-transaction.save');
                Route::post('delete/{id}', [FinanceTransactionController::class, 'delete'])->name('admin.finance-transaction.delete');
            });

            Route::prefix('cashier-terminals')->group(function () {
                Route::get('', [CashierTerminalController::class, 'index'])->name('admin.cashier-terminal.index');
                Route::get('data', [CashierTerminalController::class, 'data'])->name('admin.cashier-terminal.data');
                Route::get('add', [CashierTerminalController::class, 'editor'])->name('admin.cashier-terminal.add');
                Route::get('edit/{id}', [CashierTerminalController::class, 'editor'])->name('admin.cashier-terminal.edit');
                Route::get('detail/{id}', [CashierTerminalController::class, 'detail'])->name('admin.cashier-terminal.detail');
                Route::post('save', [CashierTerminalController::class, 'save'])->name('admin.cashier-terminal.save');
                Route::post('delete/{id}', [CashierTerminalController::class, 'delete'])->name('admin.cashier-terminal.delete');
            });

            Route::prefix('cashier-sessions')->group(function () {
                Route::get('', [CashierSessionController::class, 'index'])->name('admin.cashier-session.index');
                Route::get('data', [CashierSessionController::class, 'data'])->name('admin.cashier-session.data');
                Route::match(['get', 'post'], 'open', [CashierSessionController::class, 'open'])->name('admin.cashier-session.open');
                Route::match(['get', 'post'], 'close/{id}', [CashierSessionController::class, 'close'])->name('admin.cashier-session.close');
                Route::get('detail/{id}', [CashierSessionController::class, 'detail'])->name('admin.cashier-session.detail');
                Route::post('delete/{id}', [CashierSessionController::class, 'delete'])->name('admin.cashier-session.delete');
            });

            Route::prefix('customers')->group(function () {
                Route::get('', [CustomerController::class, 'index'])->name('admin.customer.index');
                Route::get('data', [CustomerController::class, 'data'])->name('admin.customer.data');
                Route::match('get', 'add', [CustomerController::class, 'editor'])->name('admin.customer.add');
                Route::match('get', 'duplicate/{id}', [CustomerController::class, 'duplicate'])->name('admin.customer.duplicate');
                Route::match('get', 'edit/{id}', [CustomerController::class, 'editor'])->name('admin.customer.edit');
                Route::get('detail/{id}', [CustomerController::class, 'detail'])->name('admin.customer.detail');
                Route::post('save', [CustomerController::class, 'save'])->name('admin.customer.save');
                Route::post('delete/{id}', [CustomerController::class, 'delete'])->name('admin.customer.delete');
                Route::get('wallet_balance', [CustomerController::class, 'getBalance'])->name('admin.customer.wallet_balance');
                Route::match(['get', 'post'], 'import', [CustomerController::class, 'import'])->name('admin.customer.import');
            });

            Route::prefix('customer-wallet-transactions')->group(function () {
                Route::get('', [CustomerWalletTransactionController::class, 'index'])->name('admin.customer-wallet-transaction.index');
                Route::get('data', [CustomerWalletTransactionController::class, 'data'])->name('admin.customer-wallet-transaction.data');
                Route::get('add', [CustomerWalletTransactionController::class, 'editor'])->name('admin.customer-wallet-transaction.add');
                Route::get('edit/{id}', [CustomerWalletTransactionController::class, 'editor'])->name('admin.customer-wallet-transaction.edit');
                Route::get('detail/{id}', [CustomerWalletTransactionController::class, 'detail'])->name('admin.customer-wallet-transaction.detail');
                Route::post('save', [CustomerWalletTransactionController::class, 'save'])->name('admin.customer-wallet-transaction.save');
                Route::match(['get', 'post'], 'adjustment', [CustomerWalletTransactionController::class, 'adjustment'])->name('admin.customer-wallet-transaction.adjustment');
                Route::post('delete/{id}', [CustomerWalletTransactionController::class, 'delete'])->name('admin.customer-wallet-transaction.delete');
            });

            Route::prefix('customer-wallet-transaction-confirmations')->group(function () {
                Route::get('', [CustomerWalletTransactionConfirmationController::class, 'index'])->name('admin.customer-wallet-transaction-confirmation.index');
                Route::get('data', [CustomerWalletTransactionConfirmationController::class, 'data'])->name('admin.customer-wallet-transaction-confirmation.data');
                Route::get('detail/{id}', [CustomerWalletTransactionConfirmationController::class, 'detail'])->name('admin.customer-wallet-transaction-confirmation.detail');
                Route::post('save', [CustomerWalletTransactionConfirmationController::class, 'save'])->name('admin.customer-wallet-transaction-confirmation.save');
                Route::post('delete/{id}', [CustomerWalletTransactionConfirmationController::class, 'delete'])->name('admin.customer-wallet-transaction-confirmation.delete');
            });

            Route::prefix('suppliers')->group(function () {
                Route::get('', [SupplierController::class, 'index'])->name('admin.supplier.index');
                Route::get('data', [SupplierController::class, 'data'])->name('admin.supplier.data');
                Route::get('add', [SupplierController::class, 'editor'])->name('admin.supplier.add');
                Route::get('duplicate/{id}', [SupplierController::class, 'duplicate'])->name('admin.supplier.duplicate');
                Route::get('edit/{id}', [SupplierController::class, 'editor'])->name('admin.supplier.edit');
                Route::get('detail/{id}', [SupplierController::class, 'detail'])->name('admin.supplier.detail');
                Route::post('save', [SupplierController::class, 'save'])->name('admin.supplier.save');
                Route::post('delete/{id}', [SupplierController::class, 'delete'])->name('admin.supplier.delete');
            });

            Route::prefix('operational-cost-categories')->group(function () {
                Route::get('', [OperationalCostCategoryController::class, 'index'])->name('admin.operational-cost-category.index');
                Route::get('data', [OperationalCostCategoryController::class, 'data'])->name('admin.operational-cost-category.data');
                Route::get('add', [OperationalCostCategoryController::class, 'editor'])->name('admin.operational-cost-category.add');
                Route::get('duplicate/{id}', [OperationalCostCategoryController::class, 'duplicate'])->name('admin.operational-cost-category.duplicate');
                Route::get('edit/{id}', [OperationalCostCategoryController::class, 'editor'])->name('admin.operational-cost-category.edit');
                Route::post('save', [OperationalCostCategoryController::class, 'save'])->name('admin.operational-cost-category.save');
                Route::post('delete/{id}', [OperationalCostCategoryController::class, 'delete'])->name('admin.operational-cost-category.delete');
            });

            Route::prefix('operational-costs')->group(function () {
                Route::get('', [OperationalCostController::class, 'index'])->name('admin.operational-cost.index');
                Route::get('data', [OperationalCostController::class, 'data'])->name('admin.operational-cost.data');
                Route::get('add', [OperationalCostController::class, 'editor'])->name('admin.operational-cost.add');
                Route::get('duplicate/{id}', [OperationalCostController::class, 'duplicate'])->name('admin.operational-cost.duplicate');
                Route::get('edit/{id}', [OperationalCostController::class, 'editor'])->name('admin.operational-cost.edit');
                Route::post('save', [OperationalCostController::class, 'save'])->name('admin.operational-cost.save');
                Route::post('delete/{id}', [OperationalCostController::class, 'delete'])->name('admin.operational-cost.delete');
                Route::get('detail/{id}', [OperationalCostController::class, 'detail'])->name('admin.operational-cost.detail');
            });

            Route::prefix('purchase-orders')->group(function () {
                Route::get('', [PurchaseOrderController::class, 'index'])->name('admin.purchase-order.index');
                Route::get('data', [PurchaseOrderController::class, 'data'])->name('admin.purchase-order.data');
                Route::get('add', [PurchaseOrderController::class, 'editor'])->name('admin.purchase-order.add');
                Route::get('edit/{id}', [PurchaseOrderController::class, 'editor'])->name('admin.purchase-order.edit');
                Route::get('detail/{id}', [PurchaseOrderController::class, 'detail'])->name('admin.purchase-order.detail');
                Route::post('save', [PurchaseOrderController::class, 'save'])->name('admin.purchase-order.save');
                Route::post('cancel/{id}', [PurchaseOrderController::class, 'cancel'])->name('admin.purchase-order.cancel');
                Route::post('delete/{id}', [PurchaseOrderController::class, 'delete'])->name('admin.purchase-order.delete');
                Route::post('update', [PurchaseOrderController::class, 'update'])->name('admin.purchase-order.update');
                Route::post('close', [PurchaseOrderController::class, 'close'])->name('admin.purchase-order.close');
                Route::post('add-payment', [PurchaseOrderController::class, 'addPayment'])->name('admin.purchase-order.add-payment');
                Route::post('delete-payment', [PurchaseOrderController::class, 'deletePayment'])->name('admin.purchase-order.delete-payment');
                // items
                Route::post('add-item', [PurchaseOrderController::class, 'addItem'])->name('admin.purchase-order.add-item');
                Route::post('remove-item', [PurchaseOrderController::class, 'removeItem'])->name('admin.purchase-order.remove-item');
                Route::post('update-item', [PurchaseOrderController::class, 'updateItem'])->name('admin.purchase-order.update-item');
            });

            Route::prefix('purchase-order-returns')->group(function () {
                Route::get('', [PurchaseOrderReturnController::class, 'index'])->name('admin.purchase-order-return.index');
                Route::get('data', [PurchaseOrderReturnController::class, 'data'])->name('admin.purchase-order-return.data');
                Route::get('add', [PurchaseOrderReturnController::class, 'editor'])->name('admin.purchase-order-return.add');
                Route::match(['get', 'post'], 'add', [PurchaseOrderReturnController::class, 'add'])->name('admin.purchase-order-return.add');
                Route::get('edit/{id}', [PurchaseOrderReturnController::class, 'editor'])->name('admin.purchase-order-return.edit');
                Route::get('detail/{id}', [PurchaseOrderReturnController::class, 'detail'])->name('admin.purchase-order-return.detail');
                Route::post('save', [PurchaseOrderReturnController::class, 'save'])->name('admin.purchase-order-return.save');
                Route::post('cancel/{id}', [PurchaseOrderReturnController::class, 'cancel'])->name('admin.purchase-order-return.cancel');
                Route::post('delete/{id}', [PurchaseOrderReturnController::class, 'delete'])->name('admin.purchase-order-return.delete');
                Route::post('update', [PurchaseOrderReturnController::class, 'update'])->name('admin.purchase-order-return.update');
                Route::post('close', [PurchaseOrderReturnController::class, 'close'])->name('admin.purchase-order-return.close');
                Route::post('add-refund', [PurchaseOrderReturnController::class, 'addRefund'])->name('admin.purchase-order-return.add-refund');
                Route::post('delete-refund', [PurchaseOrderReturnController::class, 'deleteRefund'])->name('admin.purchase-order-return.delete-refund');
                // items
                Route::post('add-item', [PurchaseOrderReturnController::class, 'addItem'])->name('admin.purchase-order-return.add-item');
                Route::post('remove-item', [PurchaseOrderReturnController::class, 'removeItem'])->name('admin.purchase-order-return.remove-item');
                Route::post('update-item', [PurchaseOrderReturnController::class, 'updateItem'])->name('admin.purchase-order-return.update-item');
            });

            Route::prefix('sales-orders')->group(function () {
                Route::get('', [SalesOrderController::class, 'index'])->name('admin.sales-order.index');
                Route::get('data', [SalesOrderController::class, 'data'])->name('admin.sales-order.data');
                Route::get('add', [SalesOrderController::class, 'editor'])->name('admin.sales-order.add');
                Route::get('edit/{id}', [SalesOrderController::class, 'editor'])->name('admin.sales-order.edit');
                Route::get('detail/{id}', [SalesOrderController::class, 'detail'])->name('admin.sales-order.detail');
                Route::post('save', [SalesOrderController::class, 'save'])->name('admin.sales-order.save');
                Route::post('cancel/{id}', [SalesOrderController::class, 'cancel'])->name('admin.sales-order.cancel');
                Route::post('delete/{id}', [SalesOrderController::class, 'delete'])->name('admin.sales-order.delete');
                Route::get('print/{id}', [SalesOrderController::class, 'print'])->name('admin.sales-order.print');
                Route::post('update', [SalesOrderController::class, 'update'])->name('admin.sales-order.update');
                Route::post('close', [SalesOrderController::class, 'close'])->name('admin.sales-order.close');

                // payment
                Route::post('add-payment', [SalesOrderController::class, 'addPayment'])->name('admin.sales-order.add-payment');
                Route::post('delete-payment', [SalesOrderController::class, 'deletePayment'])->name('admin.sales-order.delete-payment');

                // items
                Route::post('add-item', [SalesOrderController::class, 'addItem'])->name('admin.sales-order.add-item');
                Route::post('remove-item', [SalesOrderController::class, 'removeItem'])->name('admin.sales-order.remove-item');
                Route::post('update-item', [SalesOrderController::class, 'updateItem'])->name('admin.sales-order.update-item');
            });

            Route::prefix('sales-order-returns')->group(function () {
                Route::get('', [SalesOrderReturnController::class, 'index'])->name('admin.sales-order-return.index');
                Route::get('data', [SalesOrderReturnController::class, 'data'])->name('admin.sales-order-return.data');
                Route::match(['get', 'post'], 'add', [SalesOrderReturnController::class, 'add'])->name('admin.sales-order-return.add');
                Route::get('edit/{id}', [SalesOrderReturnController::class, 'editor'])->name('admin.sales-order-return.edit');
                Route::get('detail/{id}', [SalesOrderReturnController::class, 'detail'])->name('admin.sales-order-return.detail');
                Route::post('save', [SalesOrderReturnController::class, 'save'])->name('admin.sales-order-return.save');
                Route::post('cancel/{id}', [SalesOrderReturnController::class, 'cancel'])->name('admin.sales-order-return.cancel');
                Route::post('delete/{id}', [SalesOrderReturnController::class, 'delete'])->name('admin.sales-order-return.delete');
                Route::get('print/{id}', [SalesOrderReturnController::class, 'print'])->name('admin.sales-order-return.print');
                Route::post('update', [SalesOrderReturnController::class, 'update'])->name('admin.sales-order-return.update');
                Route::post('close', [SalesOrderReturnController::class, 'close'])->name('admin.sales-order-return.close');

                // payment
                Route::post('add-refund', [SalesOrderReturnController::class, 'addRefund'])->name('admin.sales-order-return.add-refund');
                Route::post('delete-refund', [SalesOrderReturnController::class, 'deleteRefund'])->name('admin.sales-order-return.delete-refund');

                // items
                Route::post('add-item', [SalesOrderReturnController::class, 'addItem'])->name('admin.sales-order-return.add-item');
                Route::post('remove-item', [SalesOrderReturnController::class, 'removeItem'])->name('admin.sales-order-return.remove-item');
                Route::post('update-item', [SalesOrderReturnController::class, 'updateItem'])->name('admin.sales-order-return.update-item');
            });

            Route::prefix('document-versions')->group(function () {
                Route::get('data', [DocumentVersionController::class, 'data'])->name('admin.document-version.data');
            });

            Route::prefix('settings')->group(function () {

                Route::prefix('database')->group(function () {
                    Route::get('', [DatabaseSettingsController::class, 'index'])->name('admin.database-settings.index');
                    Route::get('logs', [DatabaseSettingsController::class, 'logs'])->name('admin.database-settings.logs');
                    Route::get('detail', [DatabaseSettingsController::class, 'logDetail'])->name('admin.database-settings.log-detail');
                    Route::get('log-data', [DatabaseSettingsController::class, 'logData'])->name('admin.database-settings.log-data');
                    Route::match(['get', 'post'], 'backup', [DatabaseSettingsController::class, 'backup'])->name('admin.database-settings.backup');
                    Route::match(['get', 'post'], 'restore', [DatabaseSettingsController::class, 'restore'])->name('admin.database-settings.restore');
                    Route::match(['get', 'post'], 'reset', [DatabaseSettingsController::class, 'reset'])->name('admin.database-settings.reset');
                });

                Route::match(['get', 'post'], 'pos', [PosSettingsController::class, 'edit'])->name('admin.pos-settings.edit');

                Route::match(['get', 'post'], 'company-profile', [CompanyProfileController::class, 'edit'])->name('admin.company-profile.edit');

                Route::prefix('user-activity-log')->group(function () {
                    Route::get('', [UserActivityLogController::class, 'index'])->name('admin.user-activity-log.index');
                    Route::get('data', [UserActivityLogController::class, 'data'])->name('admin.user-activity-log.data');
                    Route::post('delete/{id}', [UserActivityLogController::class, 'delete'])->name('admin.user-activity-log.delete');
                    Route::post('clear', [UserActivityLogController::class, 'clear'])->name('admin.user-activity-log.clear');
                    Route::get('detail/{id}', [UserActivityLogController::class, 'detail'])->name('admin.user-activity-log.detail');
                });

                Route::prefix('users')->group(function () {
                    Route::get('', [UserController::class, 'index'])->name('admin.user.index');
                    Route::get('data', [UserController::class, 'data'])->name('admin.user.data');
                    Route::get('add', [UserController::class, 'editor'])->name('admin.user.add');
                    Route::get('edit/{id}', [UserController::class, 'editor'])->name('admin.user.edit');
                    Route::get('duplicate/{id}', [UserController::class, 'duplicate'])->name('admin.user.duplicate');
                    Route::post('save', [UserController::class, 'save'])->name('admin.user.save');
                    Route::post('delete/{id}', [UserController::class, 'delete'])->name('admin.user.delete');
                    Route::get('detail/{id}', [UserController::class, 'detail'])->name('admin.user.detail');
                });

                Route::prefix('user-roles')->group(function () {
                    Route::get('', [UserRoleController::class, 'index'])->name('admin.user-role.index');
                    Route::get('data', [UserRoleController::class, 'data'])->name('admin.user-role.data');
                    Route::get('add', [UserRoleController::class, 'editor'])->name('admin.user-role.add');
                    Route::get('detail/{id}', [UserRoleController::class, 'detail'])->name('admin.user-role.detail');
                    Route::get('duplicate/{id}', [UserRoleController::class, 'duplicate'])->name('admin.user-role.duplicate');
                    Route::get('edit/{id}', [UserRoleController::class, 'editor'])->name('admin.user-role.edit');
                    Route::post('save', [UserRoleController::class, 'save'])->name('admin.user-role.save');
                    Route::post('delete/{id}', [UserRoleController::class, 'delete'])->name('admin.user-role.delete');
                });
            });
        });
    });
