<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Controllers\Api\AuthController;
use Modules\Auth\Http\Controllers\Api\DeviceController;
use Modules\Auth\Http\Controllers\Api\MobileConfigController;

Route::get('mobile/config', [MobileConfigController::class, 'config']);

Route::prefix('auth')->group(function () {
    Route::post('guest', [AuthController::class, 'guest'])
        ->middleware('mobile.feature:guest');

    Route::middleware('mobile.feature:auth')->group(function () {
        Route::post('register', [AuthController::class, 'register'])->middleware('throttle:10,1');
        Route::post('login', [AuthController::class, 'login'])->middleware('throttle:10,1');
        Route::post('send-otp', [AuthController::class, 'sendOtp'])->middleware('throttle:3,1');
        Route::post('verify-otp', [AuthController::class, 'verifyOtp'])->middleware('throttle:10,1');
        Route::post('forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:5,1');
        Route::post('reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:5,1');
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);

        Route::middleware('mobile.feature:auth')->group(function () {
            Route::put('profile', [AuthController::class, 'updateProfile']);
            Route::put('password', [AuthController::class, 'updatePassword']);
            Route::post('avatar', [AuthController::class, 'uploadAvatar']);
        });

        Route::middleware('mobile.feature:push')->group(function () {
            Route::post('devices/register', [DeviceController::class, 'register']);
            Route::delete('devices/unregister', [DeviceController::class, 'unregister']);
            Route::delete('devices', [DeviceController::class, 'unregisterAll']);
        });
    });
});
