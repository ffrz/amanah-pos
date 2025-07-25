<?php

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\Middleware\StartSession; // Import ini
use Illuminate\Cookie\Middleware\EncryptCookies; // Import ini
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse; // Import ini
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken; // Import ini
use Illuminate\Routing\Middleware\SubstituteBindings; // Import ini
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful; // Import ini
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

require_once __DIR__ . '/../app/helper.php';

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        // --- Perbaikan untuk Middleware API ---
        $middleware->api(prepend: [
            // 1. Middleware untuk menangani Cookies (Enkripsi/Dekripsi)
            EncryptCookies::class, // Penting untuk mendekripsi cookie sesi
            AddQueuedCookiesToResponse::class, // Untuk menambahkan cookie ke respons

            // 2. Middleware untuk Memulai Sesi
            StartSession::class, // KRUSIAL: Ini yang membaca cookie sesi dan memuat data sesi

            // 3. Middleware untuk Sanctum (jika Anda menggunakan SPA yang berbagi sesi)
            // Ini membantu Laravel memperlakukan permintaan dari frontend tertentu sebagai stateful.
            EnsureFrontendRequestsAreStateful::class,

            // 4. Middleware untuk CSRF (Direkomendasikan jika API berbagi sesi web)
            // Hanya aktifkan ini jika Anda mengirimkan X-CSRF-TOKEN dari frontend Anda
            // untuk request POST/PUT/DELETE. Tanpa ini, request tersebut akan ditolak.
            VerifyCsrfToken::class,

            // 5. Middleware lainnya (penting untuk Route Model Binding)
            SubstituteBindings::class,

            // 6. Rate Limiting (sesuai kebutuhan Anda)
            // 'throttle:api', // Aktifkan jika Anda ingin rate limiting default API
        ]);
        // ------------------------------------

    })
    ->withExceptions(function (Exceptions $exceptions) {
        // --- LOGIKA PENANGANAN EXCEPTION DIPINDAH DI SINI ---
        $exceptions->renderable(function (Throwable $e, $request) {
            // Periksa jika request adalah untuk API dan/atau mengharapkan JSON
            if ($request->is('api/*') || $request->expectsJson()) {
                // Tangani 404 Not Found
                if ($e instanceof NotFoundHttpException) {
                    return response()->json([
                        'message' => 'Resource not found.'
                    ], 404);
                }

                // Tangani AuthenticationException (Unauthenticated)
                if ($e instanceof AuthenticationException) {
                    return response()->json([
                        'message' => $e->getMessage() ?: 'Unauthenticated.'
                    ], 401);
                }

                // Tangani AuthorizationException (Forbidden)
                if ($e instanceof AuthorizationException) {
                    return response()->json([
                        'message' => $e->getMessage() ?: 'Unauthorized.'
                    ], 403);
                }

                // Tangani ValidationException
                if ($e instanceof ValidationException) {
                    return response()->json([
                        'message' => 'The given data was invalid.',
                        'errors' => $e->errors(),
                    ], 422);
                }

                // Tangani Exception generik lainnya
                if (app()->environment('production')) {
                    return response()->json([
                        'message' => 'An unexpected error occurred.'
                    ], 500);
                } else {
                    return response()->json([
                        'message' => $e->getMessage(),
                        'exception' => get_class($e),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTraceAsString(),
                    ], 500);
                }
            }
        });
    })->create();
