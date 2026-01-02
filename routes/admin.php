<?php

use App\Http\Controllers\Class\ClassListController;
use App\Http\Controllers\User\UserListController;
use App\Http\Controllers\User\UserEditController;
use App\Http\Controllers\Activity\ActivityListController;
use App\Http\Controllers\Activity\ActivityEditController;
use App\Http\Controllers\Todos\TodosListController;
use App\Http\Controllers\Todos\TodosEditController;

use Illuminate\Support\Facades\Route;

Route::middleware('AdminAuth')->group(function () {

    Route::prefix('activity')->middleware('ActivityRefresh')->group(function () {
        Route::post('create', [ActivityEditController::class, 'create']);
        Route::get('getList', [ActivityListController::class, 'getList']);
        Route::get('getCount', [ActivityListController::class, 'getCount']);
        Route::get('getInfo', [ActivityListController::class, 'getInfo']);
        Route::get('getHomeworkTable', [ActivityListController::class, 'getHomeworkTable']);
        Route::get('getTable', [ActivityListController::class, 'getTable']);
        Route::post('edit', [ActivityEditController::class, 'edit']);
        Route::post('editTime', [ActivityEditController::class, 'editTime']);
        Route::post('delete', [ActivityEditController::class, 'delete']);
        Route::post('export', [ActivityListController::class, 'export']);
    });

    Route::prefix('class')->group(function () {
        Route::get('getList', [ClassListController::class, 'getList']);
    });

    Route::prefix('user')->group(function () {
        Route::post('add', [UserEditController::class, 'add']);
        Route::post('edit', [UserEditController::class, 'edit']);
        Route::get('getList', [UserListController::class, 'getList']);
        Route::post('delete', [UserEditController::class, 'delete']);
        Route::get('getCount', [UserListController::class, 'getCount']);
    });

    Route::prefix('todos')->group(function () {
        Route::get('downloadAdmin', [TodosListController::class, 'downloadAdmin']);
        Route::post('deleteAdmin', [TodosEditController::class, 'deleteAdmin']);
        Route::post('submitAdmin', [TodosEditController::class, 'submitAdmin']);
    });
});
