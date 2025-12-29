<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\StudentAuth;
use App\Http\Middleware\AdminAuth;
use App\Http\Middleware\SuperAdminAuth;
use App\Http\Middleware\ActivityRefresh;
use App\Http\Middleware\ClassRefresh;
use Illuminate\Foundation\Http\Middleware\ValidatePostSize;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

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
            ValidatePostSize::class,
            ConvertEmptyStringsToNull::class,
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
        //
    })->create();
