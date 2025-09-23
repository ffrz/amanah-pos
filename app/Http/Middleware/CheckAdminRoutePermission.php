<?php

namespace App\Http\Middleware;

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

        // Admin Bypass: Cek kolom 'role' yang sudah ada
        // Jika pengguna adalah 'admin', izinkan akses tanpa pemeriksaan lebih lanjut.
        if ($user->role === User::Role_Admin) {
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

        // Mendefinisikan rute yang akan di-bypass dari pemeriksaan izin
        $bypassRoutes = [
            'admin.dashboard',

            'admin.profile.edit',
            'admin.profile.update',
            'admin.profile.update-password',

            'admin.auth.logout',
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
