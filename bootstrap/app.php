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

use App\Helpers\JsonResponseHelper;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

require_once __DIR__ . '/../app/helper.php';

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: [
            __DIR__ . '/../routes/web.php',
            __DIR__ . '/../routes/web-api.php',
        ],
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(
            at: [
                '127.0.0.1', // Wajib: Mempercayai traffic dari Nginx/localhost
                '::1',
            ],
            headers: Request::HEADER_X_FORWARDED_FOR |
                Request::HEADER_X_FORWARDED_HOST |
                Request::HEADER_X_FORWARDED_PORT |
                Request::HEADER_X_FORWARDED_PROTO // 🚨 Wajib: Agar Laravel tahu itu HTTPS
        );

        $middleware->web(append: [
            \App\Http\Middleware\SetModuleRootView::class,
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        $middleware->append(\App\Http\Middleware\CustomMaintenanceBypass::class);

        // $middleware->redirectGuestsTo(function (Request $request) {
        //     if ($request->is('api/*') || $request->is('web-api/*') || $request->expectsJson()) {
        //         return null;
        //     }

        //     if ($request->is('admin/*') || $request->expectsJson()) {
        //         return route('admin.auth.login');
        //     }

        //     if ($request->is('customer/*') || $request->expectsJson()) {
        //         return route('customer.auth.login');
        //     }

        //     return null;
        // });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->renderable(function (Throwable $e, Request $request) {
            $customExceptions = [
                \App\Exceptions\ModelNotModifiedException::class,
                \App\Exceptions\BusinessRuleViolationException::class,
                \App\Exceptions\ModelInUseException::class,
                \App\Exceptions\OperationFailedException::class,
            ];

            $message = null;
            $statusCode = null;
            $type = 'error';

            if (in_array(get_class($e), $customExceptions)) {
                $statusCode = $e->getCode();
                $message = $e->getMessage();
            } elseif ($e->getPrevious() instanceof ModelNotFoundException) {
                $statusCode = Response::HTTP_NOT_FOUND;
                $message = $e->getMessage();
                // $message = "Rekaman tidak ditemukan.";
            } elseif ($e->getPrevious() instanceof AuthorizationException) {
                $statusCode = Response::HTTP_FORBIDDEN;
                // $message = $e->getMessage();
                $message = "Akses ditolak. Anda tidak diizinkan melakukan aksi ini.";
            }

            if ($statusCode !== null) {
                if ($request->inertia()) {
                    return back()->with($type, $message);
                }

                if ($request->expectsJson() || $request->isJson()) {
                    return JsonResponseHelper::error(
                        $message,
                        $statusCode
                    );
                }

                abort($statusCode, $message);
            }

            return null;
        });

        // $exceptions->renderable(function (Throwable $e, $request) {
        //     // Periksa jika request adalah untuk API dan/atau mengharapkan JSON
        //     if ($request->is('api/*') || $request->is('web-api/*') || $request->expectsJson()) {
        //         // Tangani 404 Not Found
        //         if ($e instanceof NotFoundHttpException) {
        //             return response()->json([
        //                 'message' => 'Resource not found.'
        //             ], 404);
        //         }

        //         // Tangani AuthenticationException (Unauthenticated)
        //         if ($e instanceof AuthenticationException) {
        //             return response()->json([
        //                 'message' => $e->getMessage() ?: 'Unauthenticated.'
        //             ], 401);
        //         }

        //         // Tangani AuthorizationException (Forbidden)
        //         if ($e instanceof AuthorizationException) {
        //             return response()->json([
        //                 'message' => $e->getMessage() ?: 'Unauthorized.'
        //             ], 403);
        //         }

        //         // Tangani ValidationException
        //         if ($e instanceof ValidationException) {
        //             return response()->json([
        //                 'message' => 'The given data was invalid.',
        //                 'errors' => $e->errors(),
        //             ], 422);
        //         }

        //         // Tangani Exception generik lainnya
        //         if (app()->environment('production')) {
        //             return response()->json([
        //                 'message' => 'An unexpected error occurred.'
        //             ], 500);
        //         } else {
        //             return response()->json([
        //                 'message' => $e->getMessage(),
        //                 'exception' => get_class($e),
        //                 'file' => $e->getFile(),
        //                 'line' => $e->getLine(),
        //                 'trace' => $e->getTraceAsString(),
        //             ], 500);
        //         }
        //     }
        // });
    })->create();
