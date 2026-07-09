<?php

use Illuminate\Support\Facades\Route;
use Modules\Appointment\Http\Controllers\Api\AppointmentController;

Route::middleware(['auth:sanctum', 'mobile.feature:booking'])->prefix('appointments')->group(function () {
    Route::post('/', [AppointmentController::class, 'book']);
    Route::get('/my', [AppointmentController::class, 'myAppointments']);
    Route::get('/{id}', [AppointmentController::class, 'show']);
    Route::post('/{id}/cancel', [AppointmentController::class, 'cancel']);
    Route::post('/{id}/confirm', [AppointmentController::class, 'confirm']);
    Route::post('/{id}/complete', [AppointmentController::class, 'complete']);
    Route::put('/{id}/reschedule', [AppointmentController::class, 'reschedule']);
});
