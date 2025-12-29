<?php

use App\Http\Controllers\Class\ClassController;
use App\Http\Controllers\Permission\EditController;
use App\Http\Controllers\Permission\GroupController;
use Illuminate\Support\Facades\Route;

Route::middleware('SuperAdminAuth')->group(function () {
    Route::prefix('class')->middleware('ClassRefresh')->group(function () {
        Route::post('add', [ClassController::class, 'add']);
        Route::post('delete', [ClassController::class, 'delete']);
        Route::get('get', [ClassController::class, 'get']);
        Route::get('getCount', [ClassController::class, 'getCount']);
        Route::post('edit', [ClassController::class, 'edit']);
        Route::post('list', [ClassController::class, 'list']);
    });

    Route::prefix('permissionEdit')->group(function () {
        Route::get('getAdminList', [EditController::class, 'getAdminList']);
        Route::get('getCount', [EditController::class, 'getCount']);
        Route::post('setAdmin', [EditController::class, 'setAdmin']);
        Route::post('addAdmin', [EditController::class, 'addAdmin']);
        Route::post('editAdmin', [EditController::class, 'editAdmin']);
        Route::post('editAdminPassword', [EditController::class, 'editAdminPassword']);
        Route::post('deleteAdmin', [EditController::class, 'deleteAdmin']);
    });

    Route::prefix('permissionGroup')->group(function () {
        Route::get('getList', [GroupController::class, 'getList']);
        Route::post('addGroup', [GroupController::class, 'addGroup']);
        Route::post('deleteGroup', [GroupController::class, 'deleteGroup']);
        Route::get('getCount', [GroupController::class, 'getCount']);
        Route::get('getGroupList', [GroupController::class, 'getGroupList']);
        Route::post('editGroup', [GroupController::class, 'editGroup']);
    });
});
