<?php

use App\Http\Middleware\ActivityRefresh;
use App\Http\Middleware\AdminAuth;
use App\Http\Middleware\ClassRefresh;
use App\Http\Middleware\StudentAuth;
use App\Http\Middleware\SuperAdminAuth;
use App\Http\Middleware\TrustProxies;
use App\Utils\ResponseTool;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: [
            __DIR__.'/../routes/guest.php',
            __DIR__.'/../routes/student.php',
            __DIR__.'/../routes/admin.php',
            __DIR__.'/../routes/superAdmin.php',
        ],

    )
    ->withMiddleware(function (Middleware $middleware): void {

        $middleware->append([
            EnsureFrontendRequestsAreStateful::class,
            ConvertEmptyStringsToNull::class,
            TrustProxies::class,
        ]);

        // 中间件别名
        $middleware->alias([
            'StudentAuth' => StudentAuth::class,
            'AdminAuth' => AdminAuth::class,
            'SuperAdminAuth' => SuperAdminAuth::class,
            'ActivityRefresh' => ActivityRefresh::class,
            'ClassRefresh' => ClassRefresh::class,
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request, \Throwable $exception): bool => $request->is('api/*') || $request->expectsJson()
        );

        $exceptions->render(function (\Throwable $exception, Request $request) {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }

            if ($exception instanceof HttpResponseException) {
                return $exception->getResponse();
            }

            if ($exception instanceof ValidationException) {
                $errors = $exception->errors();
                $firstMessage = collect($errors)->flatten()->first() ?? '请求参数验证失败';

                return ResponseTool::jsonResponse(1, $firstMessage, ['errors' => $errors], 422);
            }

            $status = match (true) {
                $exception instanceof AuthenticationException => 401,
                $exception instanceof AuthorizationException => 403,
                $exception instanceof ModelNotFoundException => 404,
                $exception instanceof HttpExceptionInterface => $exception->getStatusCode(),
                default => 500,
            };

            $message = $exception->getMessage();
            if ($status >= 500 && ! config('app.debug')) {
                $message = '服务器内部错误，请稍后重试';
            } elseif ($message === '') {
                $message = '请求处理失败';
            }

            return ResponseTool::jsonResponse(1, $message, null, $status);
        });
    })->create();
