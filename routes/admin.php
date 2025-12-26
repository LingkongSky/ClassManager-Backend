<?php

use App\Http\Controllers\ActivityCreateController;
use App\Http\Controllers\ActivityEditController;
use App\Http\Controllers\ActivityListController;

use Illuminate\Support\Facades\Route;

Route::middleware('AdminAuth')->group(function () {

 Route::prefix('activity')->group(function () {
        Route::post('create', [ActivityCreateController::class, 'create']);
        Route::get('list', [ActivityListController::class, 'list']);
        Route::post('delete', [ActivityEditController::class, 'delete']);
    });
    
});
