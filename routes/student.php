<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\Todos\TodosController;
use Illuminate\Support\Facades\Route;


Route::middleware('StudentAuth')->group(function () {
    // 用户相关
    Route::prefix('user')->group(function () {
        Route::post('logout', [LoginController::class, 'logout']);
        Route::post('editPassword', [LoginController::class, 'me']);
        Route::get('me', [LoginController::class, 'me']);
    });

    // 活动相关
    Route::prefix('todos')->middleware('ActivityRefresh')->group(function () {
        Route::get('getTodos', [TodosController::class, 'getTodos']);
        Route::get('getStarted', [TodosController::class, 'getStarted']);
        Route::get('getTodosCount', [TodosController::class, 'getTodosCount']);
        Route::get('getStartedCount', [TodosController::class, 'getStartedCount']);
        Route::get('open', [TodosController::class, 'open']);
        Route::post('submit', [TodosController::class, 'submit']);
        Route::get('download', [TodosController::class, 'download']);
        Route::post('delete', [TodosController::class, 'delete']);
    });
});
