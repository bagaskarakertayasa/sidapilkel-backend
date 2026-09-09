<?php

use App\Http\Middleware\EnsureAccountIsActive;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'account.active' => EnsureAccountIsActive::class,
            'jwt.auth'       => \App\Http\Middleware\JwtAuthenticate::class,
        ]);

        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);

        $middleware->redirectGuestsTo(fn (Request $request) => null);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'message' => $e->getMessage() ?: 'Validasi form gagal',
                    'errors'  => $e->errors(),
                ], 422);
            }
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'message' => 'Unauthenticated.',
                    'errors'  => ['Unauthorized'],
                ], 401);
            }
        });

        $exceptions->render(function (AuthorizationException|AccessDeniedHttpException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'message' => $e->getMessage() ?: 'Akses ditolak: Anda tidak memiliki izin untuk tindakan ini.',
                    'errors'  => ['Forbidden'],
                ], 403);
            }
        });

        $exceptions->render(function (ModelNotFoundException|NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'message' => 'Data tidak ditemukan.',
                    'errors'  => ['Not Found'],
                ], 404);
            }
        });

        $exceptions->render(function (ThrottleRequestsException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                $headers = $e->getHeaders();
                $retryAfter = (int) ($headers['Retry-After'] ?? 60);

                return response()->json([
                    'message'     => 'Terlalu banyak permintaan. Silakan coba lagi nanti.',
                    'retry_after' => $retryAfter,
                    'errors'      => ['Too Many Requests'],
                ], 429, $headers);
            }
        });
    })->create();
