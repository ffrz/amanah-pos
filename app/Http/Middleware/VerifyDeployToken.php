<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VerifyDeployToken
{
    public function handle(Request $request, Closure $next)
    {
        // FIXME: nyimpen di env ini salah karena jika cache config aktif di environment tertentu gak bakalan terbaca
        if (!filter_var(env('APP_MAINTENANCE_ENABLED', false), FILTER_VALIDATE_BOOLEAN)) {
            abort(404);
        }

        $token = $request->header('X-DEPLOY-TOKEN');
        if (!$token) {
            $token = $request->query('X-DEPLOY-TOKEN');
        }
        $secret = env('APP_MAINTENANCE_TOKEN');

        if (empty($token) || !hash_equals($secret, $token)) {
            Log::warning('Deployment command not authorized!', [
                'ip' => $request->ip(),
                'path' => $request->path(),
            ]);

            abort(403, 'Unauthorized');
        }

        return $next($request);
    }
}
