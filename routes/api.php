<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\SectorController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\StockPriceController;
use App\Http\Controllers\UserSettingController;
use App\Http\Controllers\VerificationController;
use Illuminate\Support\Facades\Route;

Route::apiResource('/stocks', StockController::class);
Route::apiResource('/sectors', SectorController::class);
Route::apiResource('stock_prices', StockPriceController::class);
Route::apiResource('/user/settings', UserSettingController::class);

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

// Reset Password
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetPassword'])
    ->middleware('guest')
    ->name('password.email');

Route::post('/reset-password', [PasswordResetController::class, 'reset'])
    ->middleware('guest')
    ->name('password.reset');

// protected route
Route::group([
    'middleware' => 'auth:api',
    'prefix' => 'auth',
], function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('me', [AuthController::class, 'me']);
});
