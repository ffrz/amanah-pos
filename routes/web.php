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

use App\Models\Setting;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('homepage', [
        'company_name' => Setting::value('company_name', 'Nama Koperasi'),
        'company_phone' => Setting::value('company_phone', '081xxxxxxxxx'),
        'company_email' => Setting::value('company_email', 'namakoperasi@abc.com'),
        'company_address' => Setting::value('company_address', 'Indonesia'),
    ]);
})->name('home');

Route::get('/landing-page', function () {
    return view('landing-page');
})->name('landing-page');

require_once __DIR__ . '/web-api.php'; // Rute Web API untuk AJAX yang
