<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController as ApiProductController; // Asumsikan ini untuk API Token

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| These routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group (which should be minimal).
|
*/

// --- Rute Autentikasi (Tidak memerlukan otentikasi) ---
Route::post('/v1/admin/login', [AuthController::class, 'login']); // Untuk mendapatkan API Token
Route::middleware('auth:sanctum')->post('/v1/admin/logout', [AuthController::class, 'logout']); // Logout API token

// --- Rute External API (Dilindungi oleh API Token - Sanctum) ---
// Middleware 'api' default sudah cukup karena kita akan menghapus sesi/CSRF dari group itu.
Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::get('/products', [ApiProductController::class, 'index']); // Untuk list & filtering
    Route::get('/products/{product}', [ApiProductController::class, 'show']); // Untuk detail by ID
    Route::get('/products/by-barcode/{identifier}', [ApiProductController::class, 'showByBarcode']); // Untuk detail by barcode/kode barang
    Route::post('/products', [ApiProductController::class, 'store']);
    Route::put('/products/{product}', [ApiProductController::class, 'update']);
    Route::delete('/products/{product}', [ApiProductController::class, 'destroy']);
    // Tambahkan rute API eksternal lainnya di sini
});
