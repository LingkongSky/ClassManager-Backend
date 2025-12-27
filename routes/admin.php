<?php

use App\Http\Controllers\Class\ClassController;

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

});
