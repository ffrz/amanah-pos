<?php

use App\Http\Middleware\Auth;
use Illuminate\Support\Facades\Route;
use Modules\Service\Http\Controllers\HomeController;

Route::middleware([Auth::class])->group(function () {
    Route::get('', [HomeController::class, 'index']);
    Route::get('home', [HomeController::class, 'index'])->name('service.home');
});
