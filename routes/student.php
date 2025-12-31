<?php

use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Todos\TodosListController;
use App\Http\Controllers\Todos\TodosEditController;

Route::middleware('StudentAuth')->group(function () {
    // 用户相关
    Route::prefix('user')->group(function () {
        Route::post('logout', [LoginController::class, 'logout']);
        Route::post('editPassword', [LoginController::class, 'me']);
        Route::get('me', [LoginController::class, 'me']);
    });

    // 活动相关
    Route::prefix('todos')->middleware('ActivityRefresh')->group(function () {
        Route::get('getTodos', [TodosListController::class, 'getTodos']);
        Route::get('getStarted', [TodosListController::class, 'getStarted']);
        Route::get('getTodosCount', [TodosListController::class, 'getTodosCount']);
        Route::get('getStartedCount', [TodosListController::class, 'getStartedCount']);
        Route::get('open', [TodosListController::class, 'open']);
        Route::post('submit', [TodosEditController::class, 'submit']);
        Route::get('download', [TodosListController::class, 'download']);
        Route::post('delete', [TodosEditController::class, 'delete']);
    });
});
