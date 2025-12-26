<?php

use App\Http\Controllers\Class\ClassController;
use Illuminate\Support\Facades\Route;

Route::middleware('SuperAdminAuth')->group(function () {
 Route::prefix('class')->group(function () {
        Route::post('add', [ClassController::class, 'add']);
        Route::post('delete', [ClassController::class, 'delete']);
        Route::get('get', [ClassController::class, 'get']);
        Route::get('getCount', [ClassController::class, 'getCount']);
        Route::post('edit', [ClassController::class, 'edit']);
        Route::post('list', [ClassController::class, 'list']);
    });
});

