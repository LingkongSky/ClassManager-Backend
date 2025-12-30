<?php

use App\Http\Controllers\Class\ClassController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\Activity\ListController;
use App\Http\Controllers\Activity\EditController;
use App\Http\Controllers\Todos\TodosController;

use Illuminate\Support\Facades\Route;

Route::middleware('AdminAuth')->group(function () {

    Route::prefix('activity')->middleware('ActivityRefresh')->group(function () {
        Route::post('create', [EditController::class, 'create']);
        Route::get('getList', [ListController::class, 'getList']);
        Route::get('getCount', [ListController::class, 'getCount']);
        Route::get('getInfo', [EditController::class, 'getInfo']);
        Route::get('getHomeworkTable', [ListController::class, 'getHomeworkTable']);
        Route::get('getTable', [ListController::class, 'getTable']);
        Route::post('edit', [EditController::class, 'edit']);
        Route::post('editTime', [EditController::class, 'editTime']);
        Route::post('delete', [EditController::class, 'delete']);
        Route::post('export', [ListController::class, 'export']);
    });

    Route::prefix('class')->group(function () {
        Route::get('getList', [ClassController::class, 'getList']);
    });

    Route::prefix('user')->middleware('ClassRefresh')->group(function () {
        Route::post('add', [UserController::class, 'add']);
        Route::post('edit', [UserController::class, 'edit']);
        Route::post('editPassword', [UserController::class, 'editPassword']);
        Route::get('getList', [UserController::class, 'getList']);
        Route::post('delete', [UserController::class, 'delete']);
        Route::get('getCount', [UserController::class, 'getCount']);
    });

    Route::prefix('todos')->group(function () {
        Route::get('downloadAdmin', [TodosController::class, 'downloadAdmin']);
        Route::post('deleteAdmin', [TodosController::class, 'deleteAdmin']);
        Route::post('submitAdmin', [TodosController::class, 'submitAdmin']);
    });
});
