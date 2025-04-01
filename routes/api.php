<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\SectorController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\StockPriceController;
use App\Http\Controllers\TwoFactorController;
use App\Http\Controllers\UserSettingController;
use App\Http\Controllers\VerificationEmailController;
use Illuminate\Support\Facades\Route;

Route::apiResource('/stocks', StockController::class);
Route::get('/stocks/{id}/historic', [StockPriceController::class, 'historyStockPrices']);
Route::apiResource('/sectors', SectorController::class);
Route::apiResource('stock_prices', StockPriceController::class);
Route::apiResource('/user/settings', UserSettingController::class);


// User authentication route api
Route::prefix('auth')->group(function() {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:api');
    Route::post('refresh', [AuthController::class, 'refresh'])->middleware('auth:api');
});

// email verification
Route::get('email/verify/{id}/{hash}', [VerificationEmailController::class, 'verify'])
    ->name('verification.verify');

// Reset Password
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetPassword'])
    ->name('password.email');

Route::post('/reset-password', [PasswordResetController::class, 'reset'])
    ->name('password.reset');

// Two factor
Route::post('/auth/two-factor/enable',[TwoFactorController::class, 'enable'])->middleware('auth:api');
Route::post('/auth/two-factor/disable',[TwoFactorController::class, 'disable'])->middleware('auth:api');

Route::post('/verify-token', [AuthController::class, 'verify'])->middleware('auth:api');
