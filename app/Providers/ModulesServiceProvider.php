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

namespace App\Providers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ModulesServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $modulesPath = base_path('modules');

        foreach (File::directories($modulesPath) as $modulePath) {
            $moduleName = basename($modulePath);

            // Convert CamelCase → kebab-case
            $modulePrefix = strtolower(
                preg_replace('/([a-z])([A-Z])/', '$1-$2', $moduleName)
            );

            // Web Routes
            $webRouteFile = $modulePath . '/Routes/web.php';
            if (File::exists($webRouteFile)) {
                Route::middleware('web')
                    ->prefix($modulePrefix)
                    ->group($webRouteFile);
            }

            // API Routes
            $apiRouteFile = $modulePath . '/Routes/api.php';
            if (File::exists($apiRouteFile)) {
                Route::prefix("api/{$modulePrefix}")
                    ->middleware('api')
                    ->group($apiRouteFile);
            }
        }
    }
}
