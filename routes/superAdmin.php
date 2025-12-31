<?php

use App\Http\Controllers\Class\ClassEditController;
use App\Http\Controllers\Class\ClassListController;
use App\Http\Controllers\Permission\PermissionListController;
use App\Http\Controllers\Permission\PermissionEditController;
use App\Http\Controllers\Permission\PermissionGroupListController;
use App\Http\Controllers\Permission\PermissionGroupEditController;
use Illuminate\Support\Facades\Route;

Route::middleware('SuperAdminAuth')->group(function () {
    Route::prefix('class')->middleware('ClassRefresh')->group(function () {
        Route::post('add', [ClassEditController::class, 'add']);
        Route::post('delete', [ClassEditController::class, 'delete']);
        Route::get('get', [ClassListController::class, 'get']);
        Route::get('getCount', [ClassListController::class, 'getCount']);
        Route::post('edit', [ClassEditController::class, 'edit']);
        Route::post('list', [ClassListController::class, 'list']);
    });

    Route::prefix('permissionEdit')->group(function () {
        Route::get('getAdminList', [PermissionListController::class, 'getAdminList']);
        Route::get('getCount', [PermissionListController::class, 'getCount']);
        Route::post('setAdmin', [PermissionEditController::class, 'setAdmin']);
        Route::post('addAdmin', [PermissionEditController::class, 'addAdmin']);
        Route::post('editAdmin', [PermissionEditController::class, 'editAdmin']);
        Route::post('editAdminPassword', [PermissionEditController::class, 'editAdminPassword']);
        Route::post('deleteAdmin', [PermissionEditController::class, 'deleteAdmin']);
    });

    Route::prefix('permissionGroup')->group(function () {
        Route::get('getList', [PermissionGroupListController::class, 'getList']);
        Route::post('addGroup', [PermissionGroupEditController::class, 'addGroup']);
        Route::post('deleteGroup', [PermissionGroupEditController::class, 'deleteGroup']);
        Route::get('getCount', [PermissionGroupListController::class, 'getCount']);
        Route::get('getGroupList', [PermissionGroupListController::class, 'getGroupList']);
        Route::post('editGroup', [PermissionGroupEditController::class, 'editGroup']);
    });
});
