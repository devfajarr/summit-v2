<?php

use App\Http\Middleware\EnsureKycVerified;
use App\Http\Middleware\EnsureRole;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => EnsureRole::class,
            'kyc.verified' => EnsureKycVerified::class,
        ]);
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // $exceptions->shouldRenderJsonWhen(
        //     fn (Request $request) => $request->is('api/*'),
        // );
        $exceptions->shouldRenderJsonWhen(function (Request $request, Throwable $e) {
            if ($request->is('api/*')) {
                return true;
            }

            return $request->expectsJson();
        });

        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                if ($e->getPrevious() instanceof ModelNotFoundException) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Data tidak ditemukan (404).',
                    ], 404);
                }

                return response()->json([
                    'status' => 'error',
                    'message' => 'Endpoint API tidak ditemukan (404).',
                ], 404);
            }
        });

        $exceptions->render(function (MethodNotAllowedHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Method HTTP tidak diizinkan untuk endpoint ini (405). Pastikan menggunakan method yang benar (GET/POST/PUT/DELETE).',
                ], 405);
            }
        });

        $exceptions->render(function (AccessDeniedHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Akses ditolak (403). Anda tidak memiliki izin untuk mengakses resource ini.',
                ], 403);
            }
        });

        $exceptions->render(function (AuthorizationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Akses ditolak (403). Tindakan ini tidak diizinkan untuk role Anda.',
                ], 403);
            }
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tidak diautentikasi (401). Silakan login atau sertakan Bearer Token yang valid.',
                ], 401);
            }
        });

        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data yang dikirimkan tidak valid.',
                    'errors' => $e->errors(),
                ], 422);
            }
        });

        $exceptions->render(function (Throwable $e, Request $request) {

            if ($request->is('api/*')) {

                $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;

                $message = config('app.debug') ? $e->getMessage() : 'Terjadi kesalahan pada server. Mohon hubungi admin.';

                return response()->json([
                    'status' => 'error',
                    'message' => $message,
                    'debug' => config('app.debug') ? $e->getTrace() : null,
                ], $statusCode);
            }
        });
    })->create();
