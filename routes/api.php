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

Route::prefix('v1')->group(function () {
    // Public Authentication Routes
    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login'])
            ->middleware('throttle:10,1')
            ->name('auth.login');

        // Rate limit registration to prevent spam
        Route::post('/register', [AuthController::class, 'register'])
            ->middleware('throttle:10,1')
            ->name('auth.register');

        Route::get('/email/verify/{id}/{hash}', [VerificationEmailController::class, 'verify'])
            ->name('verification.verify');

        Route::post('/email/resend', [VerificationEmailController::class, 'resend'])
            ->middleware(['auth:api', 'throttle:5,1'])
            ->name('verification.resend');

        Route::post('/forgot-password', [PasswordResetController::class, 'sendResetPassword'])
            ->middleware('throttle:5,1')
            ->name('password.email');

        Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])
            ->middleware('throttle:5,1')
            ->name('password.reset');

        Route::post('/verify-otp', [TwoFactorController::class, 'verifyOtp'])->name('auth.2fa.verify');
    });

    // Protected Routes (no rate limiting unless specific need arises)
    Route::middleware('auth:api')->group(function () {
        Route::prefix('auth')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
            Route::post('/refresh', [AuthController::class, 'refresh'])->name('auth.refresh');
        });
        // LEFT PUBLIC ROUTES BELOW
    });
});

Route::apiResource('/settings', UserSettingController::class)->names('user.settings');
Route::apiResource('/stocks', StockController::class)->names('stocks');
Route::get('/stocks/{stock}/history', [StockPriceController::class, 'historyStockPrices'])
    ->name('stocks.history');
Route::apiResource('/sectors', SectorController::class)->names('sectors');
Route::apiResource('/stock-prices', StockPriceController::class)->names('stock-prices');
