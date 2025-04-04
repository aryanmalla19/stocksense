<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\HoldingController;
use App\Http\Controllers\IpoApplicationController;
use App\Http\Controllers\IpoDetailController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\SectorController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\StockPriceController;
use App\Http\Controllers\TwoFactorController;
use App\Http\Controllers\UserSettingController;
use App\Http\Controllers\VerificationEmailController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware(\App\Http\Middleware\ApiExceptionMiddleware::class)->group(function () {

    // Public Authentication Routes
    Route::prefix('auth')->group(function () {

        // Rate-limited authentication actions
        Route::middleware('throttle:10,1')->group(function () {
            Route::post('/login', [AuthController::class, 'login'])->name('auth.login');
            Route::post('/register', [AuthController::class, 'register'])->name('auth.register');
            Route::post('/refresh', [AuthController::class, 'refresh'])->name('auth.refresh');      
        });

        // Rate-limited email & password management actions
        Route::middleware('throttle:100,1')->group(function () {
            Route::get('/email/verify/{id}/{hash}', [VerificationEmailController::class, 'verify'])->name('verification.verify');
            Route::post('/email/resend', [VerificationEmailController::class, 'resend'])->name('verification.resend');
            Route::post('/forgot-password', [PasswordResetController::class, 'sendResetPassword'])->name('password.forgot');
            Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])->name('password.reset');
            Route::post('/verify-otp', [TwoFactorController::class, 'verifyOtp'])->name('otp.verify');
        });
    });

    // Protected Routes (Require Authentication)
    Route::middleware('auth:api')->group(function () {

        // Authenticated User Actions
        Route::prefix('auth')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
        });

        // User Settings
        Route::apiResource('/users/settings', UserSettingController::class)->names('user.settings');

        // Stocks & Stock Prices
        Route::apiResource('/stocks', StockController::class)->names('stocks');
        Route::get('/stocks/{stock}/history', [StockPriceController::class, 'historyStockPrices'])->name('stocks.history');
        Route::apiResource('/stock-prices', StockPriceController::class)->names('stock-prices');
        Route::apiResource('/users/portfolios', PortfolioController::class)->names('users.portfolios');
        Route::apiResource('/users/{id}/holdings', HoldingController::class)->names('users.holdings');
        // IPO Management
        Route::apiResource('/ipo-details', IpoDetailController::class)->names('ipo-details');
        Route::apiResource('/ipo-applications', IpoApplicationController::class)->names('ipo-applications');

        // Sectorsa
        Route::apiResource('/sectors', SectorController::class)->names('sectors');
    });
});
