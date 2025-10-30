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

namespace App\Http\Middleware;

use App\Constants\AppPermissions;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User; // Pastikan model User diimpor

class CheckAdminRoutePermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Pastikan pengguna sudah login
        if (!Auth::check()) {
            return redirect()->route('admin.auth.login');
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Bypass super user
        if ($user->type === User::Type_SuperUser) {
            return $next($request);
        }

        // Dapatkan named route yang sedang diakses
        $routeName = $request->route()?->getName();

        // Jika tidak ada named route, lanjutkan saja atau tolak akses
        if (is_null($routeName)) {
            // Untuk route tanpa nama, bisa diizinkan atau ditolak sesuai kebijakan
            return $next($request);
        }

        // --- BYPASS LOGIC START ---
        // perbolehkan akses ke data
        if (str_ends_with($routeName, '.data')) {
            return $next($request);
        }

        // untuk saat ini permision duplikat itu selalu sama dengan add
        if (str_ends_with($routeName, '.duplicate')) {
            $routeName = str_replace('.duplicate', '.add', $routeName);
        }

        // save gak ada di daftar permission tapi ada di route list
        if (str_ends_with($routeName, '.save')) {
            $routeName = str_replace('.save', '.add', $routeName);
        }
        // aliases
        $aliases = AppPermissions::aliases();
        if (isset($aliases[$routeName])) {
            $routeName = $aliases[$routeName];
        }

        // Mendefinisikan rute yang akan di-bypass dari pemeriksaan izin
        $bypassRoutes = [
            // 'admin.dashboard',
            'admin.home',

            'admin.profile.edit',
            'admin.profile.update',
            'admin.profile.update-password',

            'admin.auth.logout',

            // route berikut ini harus pakai authorization
            'admin.sales-order.add-item',
            'admin.sales-order.remove-item',
            'admin.sales-order.update',
            'admin.sales-order.update-item',
            // Tambahkan rute lain yang tidak memerlukan izin
        ];

        // Bypass jika rute ada dalam daftar pengecualian
        if (in_array($routeName, $bypassRoutes)) {
            return $next($request);
        }
        // --- BYPASS LOGIC END ---

        // Periksa Izin: Jika bukan admin, periksa apakah pengguna memiliki izin
        // dengan nama yang sama dengan rute
        if ($user->can($routeName)) {
            return $next($request);
        }

        // Jika tidak memiliki izin dan bukan admin, tolak akses
        abort(403, 'Anda tidak memiliki izin untuk mengakses halaman ini.');
    }
}
