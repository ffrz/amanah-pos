<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User; // Import model User Anda untuk konstanta peran

/**
 * Class CheckRoutePermission
 *
 * Middleware ini memeriksa izin akses berdasarkan named route yang sedang diakses.
 * Pengguna dengan peran 'admin' akan otomatis melewati semua pemeriksaan izin.
 */
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
            // Jika tidak login, redirect ke halaman login
            return redirect()->route('admin.auth.login');
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Admin Bypass: Jika pengguna adalah 'admin', izinkan akses tanpa pemeriksaan lebih lanjut
        if ($user->role === User::Role_Admin) {
            return $next($request);
        }

        // Dapatkan named route yang sedang diakses
        $routeName = $request->route()->getName();

        // --- BYPASS LOGIC START ---
        // Anda bisa mendefinisikan daftar route yang akan di-bypass di sini.
        // Ini bisa berupa array nama route spesifik atau menggunakan wildcard/regex.

        $bypassRoutes = [
            // 'admin.product.data',
        ];

        // Bypass berdasarkan daftar nama route spesifik
        if (in_array($routeName, $bypassRoutes)) {
            return $next($request);
        }
        // --- BYPASS LOGIC END ---

        // Jika route tidak memiliki nama (misalnya, closure route tanpa name()),
        // maka secara default izinkan akses atau tangani sesuai kebijakan Anda.
        // Untuk keamanan, lebih baik tolak jika tidak ada named route yang jelas.
        if (is_null($routeName)) {
            abort(403, 'Akses ditolak: Route tidak memiliki nama yang terdefinisi untuk pemeriksaan izin.');
        }

        // Periksa Izin: Jika bukan admin, periksa apakah pengguna memiliki izin dengan nama yang sama dengan route
        // Spatie's can() method akan memeriksa izin yang diberikan langsung ke user atau melalui peran mereka
        if ($user->can($routeName)) {
            return $next($request);
        }

        // Jika tidak memiliki izin dan bukan admin, tolak akses
        abort(403, 'Anda tidak memiliki izin untuk mengakses halaman ini.');
    }
}
