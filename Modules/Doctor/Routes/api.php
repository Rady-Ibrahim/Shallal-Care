<?php

use Illuminate\Support\Facades\Route;
use Modules\Doctor\Http\Controllers\Api\DoctorController;
use Modules\Doctor\Http\Controllers\Api\DoctorBranchController;
use Modules\Doctor\Http\Controllers\Api\GovernorateController;

Route::get('governorates', [GovernorateController::class, 'index'])
    ->middleware('mobile.feature:directory');

Route::prefix('doctors')->middleware('mobile.feature:directory')->group(function () {
    Route::get('/', [DoctorController::class, 'index']);
    Route::get('/featured', [DoctorController::class, 'featured']);
    Route::get('/specialities', [DoctorController::class, 'specialities']);

    Route::middleware('mobile.feature:nearby')->group(function () {
        Route::get('/nearby', [DoctorController::class, 'nearby']);
        Route::get('/branches/nearby', [DoctorBranchController::class, 'nearby']);
    });

    Route::get('/branches/{branchId}', [DoctorBranchController::class, 'show']);

    Route::get('/{id}', [DoctorController::class, 'show']);
    Route::get('/{id}/schedule', [DoctorController::class, 'schedule']);
    Route::get('/{id}/branches', [DoctorBranchController::class, 'index']);
});
