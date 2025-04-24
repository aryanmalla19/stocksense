<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\HoldingController;
use App\Http\Controllers\IpoApplicationController;
use App\Http\Controllers\IpoDetailController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\SectorController;
use App\Http\Controllers\SseController;
use App\Http\Controllers\SocialiteController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\StockPriceController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\TwoFactorController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VerificationEmailController;
use App\Http\Controllers\WatchlistController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // Public Authentication Routes
    Route::prefix('auth')->group(function () {
        // Rate-limited authentication actions
        Route::middleware('throttle:10,1')->group(function () {
            Route::post('/login', [AuthController::class, 'login'])->name('auth.login');
            Route::post('/register', [AuthController::class, 'register'])->name('auth.register');
            Route::post('/refresh', [AuthController::class, 'refresh'])->name('auth.refresh');
            Route::post('/google/callback', [SocialiteController::class, 'handleGoogleCallback'])->name('auth.google');
        });

        // Rate-limited email & password management actions
        Route::middleware('throttle:100,1')->group(function () {
            Route::get('/email/verify/{id}/{hash}', [VerificationEmailController::class, 'verify'])
                ->name('verification.verify')
                ->middleware('signed');
            Route::post('/email/resend', [VerificationEmailController::class, 'resend'])->name('verification.resend');
            Route::post('/forgot-password', [PasswordResetController::class, 'sendResetPassword'])->name('password.forgot');
            Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])->name('password.reset');
            Route::post('/verify-otp', [TwoFactorController::class, 'verifyOtp'])->name('otp.verify');
            Route::get('/sse-notifications', [SseController::class, 'stream']);
        });
    });

    // Protected Routes (Require Authentication)
    Route::middleware('auth:api')->group(function () {
        // Authenticated User Actions
        Route::prefix('auth')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
            Route::post('/2fa/enable', [TwoFactorController::class, 'enable'])->name('2fa.enable');
            Route::post('/2fa/disable', [TwoFactorController::class, 'disable'])->name('2fa.disable');
            Route::post('/change-password', [AuthController::class, 'changePassword']);
        });

        Route::get('/profile', [UserController::class, 'index']);
        Route::put('/profile', [UserController::class, 'update']);
        // Stocks & Stock Prices
        Route::apiResource('/stocks', StockController::class)->names('stocks')
            ->only(['index', 'show']);
        Route::apiResource('/stocks', StockController::class)->names('stocks')
            ->only(['store', 'update', 'destroy'])->middleware('isAdmin');
        Route::get('/stocks/{stock}/history', [StockPriceController::class, 'historyStockPrices'])->name('stocks.history');


        Route::get('/portfolios', PortfolioController::class)->name('portfolios');
        Route::apiResource('/holdings', HoldingController::class)
            ->only(['index', 'show'])
            ->names('holdings');

        // IPO Management
        Route::apiResource('/ipo-details', IpoDetailController::class)->names('ipo-details');
        Route::apiResource('/ipo-applications', IpoApplicationController::class)
            ->only(['index', 'show', 'store'])
            ->names('ipo-applications');

        // Sectors
        Route::apiResource('/sectors', SectorController::class)
            ->only(['index','show'])
            ->names('sectors');
        Route::apiResource('/sectors', SectorController::class)
            ->only(['store','update', 'destroy'])
            ->names('sectors')
            ->middleware('auth:api');

        // Transaction
        Route::apiResource('/transactions', TransactionController::class)
            ->only(['show', 'index', 'store'])
            ->names('transactions');

        // Watchlist
        Route::apiResource('/watchlists', WatchlistController::class)
        ->only(['index', 'store', 'destroy']);

        // Notifications
        Route::get('/users/notifications', NotificationController::class);

        // Admin
        Route::middleware('isAdmin')->prefix('admin')->group(function () {
            Route::get('/ipo-details', [IpoDetailController::class, 'adminIndex']);
        });
    });
});

Route::get('/stocks/{stock}/history', [StockPriceController::class, 'historyStockPricesLive'])->name('stocks.history');
