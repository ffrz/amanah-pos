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
use Illuminate\Http\Request;
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

        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->is('api/*') || $request->is('web-api/*') || $request->expectsJson()) {
                return null;
            }

            if ($request->is('admin/*') || $request->expectsJson()) {
                return route('admin.auth.login');
            }

            if ($request->is('customer/*') || $request->expectsJson()) {
                return route('customer.auth.login');
            }

            return null;
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->renderable(function (Throwable $e, $request) {
            // Periksa jika request adalah untuk API dan/atau mengharapkan JSON
            if ($request->is('api/*') || $request->is('web-api/*') || $request->expectsJson()) {
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
