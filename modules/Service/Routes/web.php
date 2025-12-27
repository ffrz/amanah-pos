<?php

use App\Http\Middleware\Auth;
use Illuminate\Support\Facades\Route;
use Modules\Service\Http\Controllers\HomeController;
use Modules\Service\Http\Controllers\TechnicianController;
use Modules\Service\Http\Controllers\ServiceOrderController; // Import Controller baru

Route::middleware([Auth::class])->group(function () {
    Route::get('', [HomeController::class, 'index']);
    Route::get('home', [HomeController::class, 'index'])->name('service.home');

    // --- Manajemen Teknisi ---
    Route::prefix('technicians')->group(function () {
        Route::get('', [TechnicianController::class, 'index'])->name('service.technician.index');
        Route::get('data', [TechnicianController::class, 'data'])->name('service.technician.data');
        Route::match(['get'], 'add', [TechnicianController::class, 'editor'])->name('service.technician.add');
        Route::match(['get'], 'duplicate/{id}', [TechnicianController::class, 'duplicate'])->name('service.technician.duplicate');
        Route::match(['get'], 'edit/{id}', [TechnicianController::class, 'editor'])->name('service.technician.edit');
        Route::get('detail/{id}', [TechnicianController::class, 'detail'])->name('service.technician.detail');
        Route::post('save', [TechnicianController::class, 'save'])->name('service.technician.save');
        Route::post('delete/{id}', [TechnicianController::class, 'delete'])->name('service.technician.delete');
    });

    // --- Manajemen Order Service ---
    Route::prefix('service-orders')->group(function () {
        Route::get('', [ServiceOrderController::class, 'index'])->name('service.service-order.index');
        Route::get('data', [ServiceOrderController::class, 'data'])->name('service.service-order.data');
        Route::match(['get'], 'add', [ServiceOrderController::class, 'editor'])->name('service.service-order.add');
        Route::match(['get'], 'duplicate/{id}', [ServiceOrderController::class, 'duplicate'])->name('service.service-order.duplicate');
        Route::match(['get'], 'edit/{id}', [ServiceOrderController::class, 'editor'])->name('service.service-order.edit');
        Route::get('detail/{id}', [ServiceOrderController::class, 'detail'])->name('service.service-order.detail');
        Route::post('save', [ServiceOrderController::class, 'save'])->name('service.service-order.save');
        Route::post('delete/{id}', [ServiceOrderController::class, 'delete'])->name('service.service-order.delete');
    });
});
