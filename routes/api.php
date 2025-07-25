<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Middleware\Auth;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware([Auth::class])->prefix('v1')->group(function () {
    Route::get('/products', [ProductController::class, 'index']); // Untuk list & filtering
    Route::get('/products/{product}', [ProductController::class, 'show']); // Untuk detail by ID
    Route::get('/products/by-barcode/{identifier}', [ProductController::class, 'showByBarcode']); // Untuk detail by barcode/kode barang
    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{product}', [ProductController::class, 'update']);
    Route::delete('/products/{product}', [ProductController::class, 'destroy']);
});
