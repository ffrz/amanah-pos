<?php

use App\Http\Middleware\Auth;
use Illuminate\Support\Facades\Route;
use Modules\Service\Http\Controllers\HomeController;
use Modules\Service\Http\Controllers\TechnicianController;

Route::middleware([Auth::class])->group(function () {
    Route::get('', [HomeController::class, 'index']);
    Route::get('home', [HomeController::class, 'index'])->name('service.home');


    Route::prefix('technicians')->group(function () {
        Route::get('', [TechnicianController::class, 'index'])->name('service.technician.index');
        Route::get('data', [TechnicianController::class, 'data'])->name('service.technician.data');
        Route::match('get', 'add', [TechnicianController::class, 'editor'])->name('service.technician.add');
        Route::match('get', 'duplicate/{id}', [TechnicianController::class, 'duplicate'])->name('service.technician.duplicate');
        Route::match('get', 'edit/{id}', [TechnicianController::class, 'editor'])->name('service.technician.edit');
        Route::get('detail/{id}', [TechnicianController::class, 'detail'])->name('service.technician.detail');
        Route::post('save', [TechnicianController::class, 'save'])->name('service.technician.save');
        Route::post('delete/{id}', [TechnicianController::class, 'delete'])->name('service.technician.delete');
    });
});
