<?php

namespace App\Http\Middleware;

use Closure;

class CustomMaintenanceBypass
{
    public function handle($request, Closure $next)
    {
        // FIXME: nyimpen di env ini salah
        $maintenance_mode = env('APP_MAINTENANCE_STATUS', 'up');

        if ($maintenance_mode === 'down') {
            $secret = env('APP_MAINTENANCE_TOKEN');
            $token = $request->header('X-DEPLOY-TOKEN');
            if (!$token) {
                $token = $request->query('X-DEPLOY-TOKEN');
            }

            if (str_starts_with(trim($request->path(), '/'), 'sysmain') && $token === $secret) {
                return $next($request);
            }

            abort(503, 'The application is currently down for maintenance.');
        }

        return $next($request);
    }
}
