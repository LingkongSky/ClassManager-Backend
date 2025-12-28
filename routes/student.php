<?php

use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;


Route::middleware('StudentAuth')->group(function () {
    // 用户相关
    Route::prefix('user')->group(function () {
        Route::post('logout', [LoginController::class, 'logout']);
        Route::post('editPassword', [LoginController::class, 'me']);
        Route::get('me', [LoginController::class, 'me']);
    });
});
