<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CompanyProfileController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\CustomerWalletTransactionController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OperationalCostCategoryController;
use App\Http\Controllers\Admin\ProductCategoryController;
use App\Http\Controllers\Admin\OperationalCostController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\StockAdjustmentController;
use App\Http\Controllers\Admin\StockMovementController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\UserController;

use App\Http\Middleware\Auth;
use App\Http\Middleware\NonAuthenticated;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('homepage');
})->name('home');

Route::middleware(NonAuthenticated::class)->group(function () {
    Route::prefix('/admin/auth')->group(function () {
        Route::match(['get', 'post'], 'login', [AuthController::class, 'login'])->name('admin.auth.login');
        Route::match(['get', 'post'], 'register', [AuthController::class, 'register'])->name('admin.auth.register');
        Route::match(['get', 'post'], 'forgot-password', [AuthController::class, 'forgotPassword'])->name('admin.auth.forgot-password');
    });
});

Route::middleware([Auth::class])->group(function () {
    Route::match(['get', 'post'], 'admin/auth/logout', [AuthController::class, 'logout'])->name('admin.auth.logout');

    Route::prefix('admin')->group(function () {
        Route::redirect('', 'admin/dashboard', 301);

        Route::get('dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
        Route::get('test', [DashboardController::class, 'test'])->name('admin.test');
        Route::get('about', function () {
            return inertia('admin/About');
        })->name('admin.about');

        Route::prefix('products')->group(function () {
            Route::get('', [ProductController::class, 'index'])->name('admin.product.index');
            Route::get('data', [ProductController::class, 'data'])->name('admin.product.data');
            Route::get('add', [ProductController::class, 'editor'])->name('admin.product.add');
            Route::get('duplicate/{id}', [ProductController::class, 'duplicate'])->name('admin.product.duplicate');
            Route::get('edit/{id}', [ProductController::class, 'editor'])->name('admin.product.edit');
            Route::post('save', [ProductController::class, 'save'])->name('admin.product.save');
            Route::post('delete/{id}', [ProductController::class, 'delete'])->name('admin.product.delete');
            Route::get('detail/{id}', [ProductController::class, 'detail'])->name('admin.product.detail');
        });

        Route::prefix('stock-adjustments')->group(function () {
            Route::get('', [StockAdjustmentController::class, 'index'])->name('admin.stock-adjustment.index');
            Route::get('data', [StockAdjustmentController::class, 'data'])->name('admin.stock-adjustment.data');
            Route::match(['get', 'post'],'create', [StockAdjustmentController::class, 'create'])->name('admin.stock-adjustment.create');
            Route::get('editor/{id}', [StockAdjustmentController::class, 'editor'])->name('admin.stock-adjustment.editor');
            Route::post('save', [StockAdjustmentController::class, 'save'])->name('admin.stock-adjustment.save');
            Route::post('delete/{id}', [StockAdjustmentController::class, 'delete'])->name('admin.stock-adjustment.delete');
            Route::get('detail/{id}', [StockAdjustmentController::class, 'detail'])->name('admin.stock-adjustment.detail');
        });

        Route::prefix('stock-movements')->group(function () {
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

        Route::prefix('customers')->group(function () {
            Route::get('', [CustomerController::class, 'index'])->name('admin.customer.index');
            Route::get('data', [CustomerController::class, 'data'])->name('admin.customer.data');
            Route::get('add', [CustomerController::class, 'editor'])->name('admin.customer.add');
            Route::get('duplicate/{id}', [CustomerController::class, 'duplicate'])->name('admin.customer.duplicate');
            Route::get('edit/{id}', [CustomerController::class, 'editor'])->name('admin.customer.edit');
            Route::get('detail/{id}', [CustomerController::class, 'detail'])->name('admin.customer.detail');
            Route::post('save', [CustomerController::class, 'save'])->name('admin.customer.save');
            Route::post('delete/{id}', [CustomerController::class, 'delete'])->name('admin.customer.delete');
        });

        Route::prefix('customer-wallet-transactions')->group(function () {
            Route::get('', [CustomerWalletTransactionController::class, 'index'])->name('admin.customer-wallet-transaction.index');
            Route::get('data', [CustomerWalletTransactionController::class, 'data'])->name('admin.customer-wallet-transaction.data');
            Route::get('add', [CustomerWalletTransactionController::class, 'editor'])->name('admin.customer-wallet-transaction.add');
            Route::get('edit/{id}', [CustomerWalletTransactionController::class, 'editor'])->name('admin.customer-wallet-transaction.edit');
            Route::get('detail/{id}', [CustomerWalletTransactionController::class, 'detail'])->name('admin.customer-wallet-transaction.detail');
            Route::post('save', [CustomerWalletTransactionController::class, 'save'])->name('admin.customer-wallet-transaction.save');
            Route::post('delete/{id}', [CustomerWalletTransactionController::class, 'delete'])->name('admin.customer-wallet-transaction.delete');
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
        });


        Route::prefix('settings')->group(function () {
            Route::get('profile/edit', [ProfileController::class, 'edit'])->name('admin.profile.edit');
            Route::post('profile/update', [ProfileController::class, 'update'])->name('admin.profile.update');
            Route::post('profile/update-password', [ProfileController::class, 'updatePassword'])->name('admin.profile.update-password');

            Route::get('company-profile/edit', [CompanyProfileController::class, 'edit'])->name('admin.company-profile.edit');
            Route::post('company-profile/update', [CompanyProfileController::class, 'update'])->name('admin.company-profile.update');

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
        });
    });
});
