<?php

use App\Http\Controllers\Class\ClassController;
use App\Http\Controllers\User\UserController;

use Illuminate\Support\Facades\Route;

Route::middleware('AdminAuth')->group(function () {
    
    Route::prefix('activity')->group(function () {
     /*   Route::post('create', [ActivityCreateController::class, 'create']);
        Route::get('list', [ListController::class, 'list']);
        Route::post('delete', [ActivityEditController::class, 'delete']);*/
    });
    
    Route::prefix('class')->group(function () {
        Route::get('getList', [ClassController::class, 'getList']);
    });

    Route::prefix('user')->group(function () {
        Route::post('add', [UserController::class, 'add']);
        Route::post('edit', [UserController::class, 'edit']);
        Route::post('editPassword', [UserController::class, 'editPassword']);
        Route::get('getList', [UserController::class, 'getList']);
        Route::post('delete', [UserController::class, 'delete']);
        Route::get('getCount', [UserController::class, 'getCount']);
    });

});
