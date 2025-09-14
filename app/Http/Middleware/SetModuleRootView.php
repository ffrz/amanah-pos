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

// App/Http/Middleware/SetModuleRootView.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class SetModuleRootView
{
    public function handle($request, Closure $next)
    {
        $firstSegment = strtolower($request->segment(1) ?? 'app');

        $viewFile = resource_path("views/modules/{$firstSegment}/app.blade.php");
        if (file_exists($viewFile)) {
            $request->attributes->set('module_root_view', $firstSegment);
            Inertia::share('currentModule', $firstSegment);
        } else {
            Inertia::share('currentModule', 'app');
        }

        return $next($request);
    }
}
