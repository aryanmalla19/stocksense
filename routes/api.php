<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VerificationController;

// User authentication route api
Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);

// email verification
Route::get('email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
    ->middleware(['signed'])
    ->name('verification.verify');

Route::post('email/verification-notification', [VerificationController::class, 'resend'])
    ->middleware(['throttle:6,1'])
    ->name('verification.send');

// protected route
Route::group([
    'middleware' => 'auth:api',
    'prefix' => 'auth',
], function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('me', [AuthController::class, 'me']);
});
