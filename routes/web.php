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

use App\Http\Controllers\Deploy\DatabaseMigrationController;
use App\Http\Controllers\Deploy\MaintenanceController;
use App\Http\Middleware\VerifyDeployToken;
use App\Models\Setting;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('homepage', [
        'company_name' => Setting::value('company.name', env('APP_NAME', 'Amanah POS')),
        'company_headline' => Setting::value('company.headline', 'Headline'),
        'company_phone' => Setting::value('company.phone', '081xxxxxxxxx'),
        'company_address' => Setting::value('company.address', 'Indonesia'),
    ]);
})->name('home');

Route::get('/landing-page', function () {
    return view('landing-page');
})->name('landing-page');

Route::middleware(VerifyDeployToken::class)->prefix('sysmain')->group(function () {
    Route::get('/status', [MaintenanceController::class, 'status']);
    Route::get('/down', [MaintenanceController::class, 'down']);
    Route::get('/up', [MaintenanceController::class, 'up']);
    Route::get('/migrate', [DatabaseMigrationController::class, 'migrate']);
});


require_once __DIR__ . '/web-api.php'; // Rute Web API untuk AJAX yang
