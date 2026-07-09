<?php

use Illuminate\Support\Facades\Route;
use Modules\MedicalRecord\Http\Controllers\Api\MedicalRecordController;

Route::middleware(['auth:sanctum', 'mobile.feature:medical_history'])->prefix('medical-records')->group(function () {
    Route::post('/', [MedicalRecordController::class, 'store']);
    Route::get('/appointment/{appointmentId}', [MedicalRecordController::class, 'show']);
    Route::get('/patient/history', [MedicalRecordController::class, 'patientHistory']);
    Route::post('/{recordId}/attachments', [MedicalRecordController::class, 'uploadAttachment']);
});
