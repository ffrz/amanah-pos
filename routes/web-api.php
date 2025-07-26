<?php

use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:web', 'web'])->prefix('web-api')->group(function () {
    Route::prefix('/products')->group(function () {
        Route::get('/', [ProductController::class, 'index'])
            ->name('web-api.products.index');
        Route::get('/find-by-barcode/{product}', [ProductController::class, 'findByBarcode'])
            ->name('web-api.products.find-by-barcode');
    });
});
