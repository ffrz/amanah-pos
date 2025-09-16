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

use App\Http\Middleware\CustomerAuth;
use App\Http\Middleware\NonAuthenticated;
use Illuminate\Support\Facades\Route;
use Modules\Customer\Http\Controllers\AuthController;
use Modules\Customer\Http\Controllers\WalletTopUpConfirmationController;
use Modules\Customer\Http\Controllers\DashboardController;
use Modules\Customer\Http\Controllers\ProfileController;
use Modules\Customer\Http\Controllers\PurchasingHistoryController;
use Modules\Customer\Http\Controllers\WalletTransactionController;

Route::middleware([NonAuthenticated::class])->group(function () {
    Route::match(['get', 'post'], 'auth/login', [AuthController::class, 'login'])->name('customer.auth.login');
});

Route::middleware([CustomerAuth::class])->group(function () {
    Route::match(['get', 'post'], 'auth/logout', [AuthController::class, 'logout'])->name('customer.auth.logout');
    Route::redirect('', 'dashboard', 301);

    Route::get('dashboard', [DashboardController::class, 'index'])->name('customer.dashboard');
    Route::get('test', [DashboardController::class, 'test'])->name('customer.test');
    Route::get('about', function () {
        return inertia('About');
    })->name('customer.about');

    Route::prefix('wallet-transactions')->group(function () {
        Route::get('', [WalletTransactionController::class, 'index'])->name('customer.wallet-transaction.index');
        Route::get('data', [WalletTransactionController::class, 'data'])->name('customer.wallet-transaction.data');
        Route::get('detail/{id}', [WalletTransactionController::class, 'detail'])->name('customer.wallet-transaction.detail');
    });

    Route::prefix('wallet-topup-confirmations')->group(function () {
        Route::get('', [WalletTopUpConfirmationController::class, 'index'])->name('customer.wallet-topup-confirmation.index');
        Route::get('add', [WalletTopUpConfirmationController::class, 'add'])->name('customer.wallet-topup-confirmation.add');
        Route::post('save', [WalletTopUpConfirmationController::class, 'save'])->name('customer.wallet-topup-confirmation.save');
        Route::get('data', [WalletTopUpConfirmationController::class, 'data'])->name('customer.wallet-topup-confirmation.data');
        Route::get('detail/{id}', [WalletTopUpConfirmationController::class, 'detail'])->name('customer.wallet-topup-confirmation.detail');
        Route::post('cancel', [WalletTopUpConfirmationController::class, 'cancel'])->name('customer.wallet-topup-confirmation.cancel');
    });

    Route::prefix('purchasing-history')->group(function () {
        Route::get('', [PurchasingHistoryController::class, 'index'])->name('customer.purchasing-history.index');
        Route::get('data', [PurchasingHistoryController::class, 'data'])->name('customer.purchasing-history.data');
        Route::get('detail/{id}', [PurchasingHistoryController::class, 'detail'])->name('customer.purchasing-history.detail');
    });

    Route::get('profile/edit', [ProfileController::class, 'edit'])->name('customer.profile.edit');
    Route::post('profile/update', [ProfileController::class, 'update'])->name('customer.profile.update');
    Route::post('profile/update-password', [ProfileController::class, 'updatePassword'])->name('customer.profile.update-password');
});
