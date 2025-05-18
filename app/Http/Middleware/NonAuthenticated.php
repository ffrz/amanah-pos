<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class NonAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $path = $request->path();

        // Cek prefix url
        $isAdminRoute = str_starts_with($path, 'admin');
        $isCustomerRoute = str_starts_with($path, 'customer');

        // Redirect jika sudah login sebagai admin
        if ($isAdminRoute && Auth::check()) {
            return redirect()->route('admin.dashboard');
        }

        // Redirect jika sudah login sebagai customer
        if ($isCustomerRoute && Auth::guard('customer')->check()) {
            return redirect()->route('customer.dashboard');
        }

        return $next($request);
    }
}
