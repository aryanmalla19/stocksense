<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\IpoApplicationController;
use App\Http\Controllers\IpoDetailController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\SectorController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\StockPriceController;
use App\Http\Controllers\TwoFactorController;
use App\Http\Controllers\UserSettingController;
use App\Http\Controllers\VerificationEmailController;
use Illuminate\Support\Facades\Route;


Route::prefix('v1')->middleware('api.exception')->group(function () {

    // Public Authentication Routes
    Route::prefix('auth')->group(function () {

        // Rate-limited authentication actions
        Route::middleware('throttle:10,1')->group(function () {
            Route::post('/login', [AuthController::class, 'login']);
            Route::post('/register', [AuthController::class, 'register']);
        });

        // Rate-limited email & password management actions
        Route::middleware('throttle:100,1')->group(function () {
            Route::get('/email/verify/{id}/{hash}', [VerificationEmailController::class, 'verify'])->name('verification.verify');
            Route::post('/email/resend', [VerificationEmailController::class, 'resend']);
            Route::post('/forgot-password', [PasswordResetController::class, 'sendResetPassword']);
            Route::post('/reset-password', [PasswordResetController::class, 'resetPassword']);
            Route::post('/verify-otp', [TwoFactorController::class, 'verifyOtp']);
        });
    });

    // Protected Routes (Require Authentication)
    Route::middleware('auth:api')->group(function () {

        // Authenticated User Actions
        Route::prefix('auth')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
            Route::post('/refresh', [AuthController::class, 'refresh'])->name('auth.refresh');
        });

        // User Settings
        Route::apiResource('/users/settings', UserSettingController::class)->names('user.settings');

        // Stocks & Stock Prices
        Route::apiResource('/stocks', StockController::class)->names('stocks');
        Route::get('/stocks/{stock}/history', [StockPriceController::class, 'historyStockPrices']);
        Route::apiResource('/stock-prices', StockPriceController::class)->names('stock-prices');

        // IPO Management
        Route::apiResource('/ipo-details', IpoDetailController::class)->names('ipo-details');
        Route::apiResource('/ipo-application', IpoApplicationController::class)->names('ipo-application');

        // Sectors
        Route::apiResource('/sectors', SectorController::class)->names('sectors');
    });
});
