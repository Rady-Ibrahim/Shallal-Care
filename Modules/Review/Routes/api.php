<?php

use Illuminate\Support\Facades\Route;
use Modules\Review\Http\Controllers\Api\ReviewController;

Route::middleware(['auth:sanctum', 'mobile.feature:reviews'])->prefix('reviews')->group(function () {
    Route::post('/', [ReviewController::class, 'create']);
    Route::get('/my', [ReviewController::class, 'myReviews']);
    Route::get('/doctor/{doctorId}', [ReviewController::class, 'doctorReviews']);
});
