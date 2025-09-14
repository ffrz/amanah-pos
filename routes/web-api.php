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

use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CustomerController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:web', 'web'])->prefix('web-api')->group(function () {
    Route::prefix('/products')->group(function () {
        Route::get('/', [ProductController::class, 'index'])
            ->name('web-api.products.index');
        Route::get('/find-by-barcode/{product}', [ProductController::class, 'findByBarcode'])
            ->name('web-api.products.find-by-barcode');
    });

    Route::prefix('/customers')->group(function () {
        Route::get('/', [CustomerController::class, 'index'])
            ->name('web-api.customers.index');
    });
});
