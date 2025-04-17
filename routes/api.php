<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HoldingController;
use App\Http\Controllers\IpoAllotmentController;
use App\Http\Controllers\IpoApplicationController;
use App\Http\Controllers\IpoDetailController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\SectorController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\StockPriceController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\TwoFactorController;
use App\Http\Controllers\UserSettingController;
use App\Http\Controllers\VerificationEmailController;
use App\Http\Controllers\WatchlistController;
use Illuminate\Http\Request; // Added this import
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
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
            Route::get('/email/verify/{id}/{hash}', [VerificationEmailController::class, 'verify'])
                ->name('verification.verify')
                ->middleware('signed');
            Route::post('/email/resend', [VerificationEmailController::class, 'resend'])->name('verification.resend');
            Route::get('/reset-password', [PasswordResetController::class, 'resetPasswordForm'])->name('password.reset.form');
            Route::post('/forgot-password', [PasswordResetController::class, 'sendResetPassword'])->name('password.forgot');
            Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])->name('password.reset');
            Route::post('/verify-otp', [TwoFactorController::class, 'verifyOtp'])->name('otp.verify');
            Route::get('/login', [AuthController::class, 'loginWithMessage'])->name('login.with-message');
        });
    });

    // Protected Routes (Require Authentication)
    Route::middleware('auth:api')->group(function () {
        // Authenticated User Actions
        Route::prefix('auth')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
            Route::get('/me', [AuthController::class, 'me'])->name('login.user');
            Route::post('/2fa/enable', [TwoFactorController::class, 'enable'])->name('2fa.enable');
            Route::post('/2fa/disable', [TwoFactorController::class, 'disable'])->name('2fa.disable');

            //change password
            Route::post('/change-password', [AuthController::class, 'changePassword']);
        });

        // User Settings
        Route::apiResource('/users/settings', UserSettingController::class)->names('user.settings');

        // Stocks & Stock Prices
        Route::apiResource('/stocks', StockController::class)->names('stocks')
            ->only(['index','show']);
        Route::apiResource('/stocks', StockController::class)->names('stocks')
            ->only(['store','update', 'destroy'])->middleware('isAdmin');
        Route::get('/stocks/{stock}/history', [StockPriceController::class, 'historyStockPrices'])->name('stocks.history');
        Route::apiResource('/stock-prices', StockPriceController::class)->names('stock-prices');
        Route::apiResource('/users/portfolios', PortfolioController::class)->names('users.portfolios');
        Route::apiResource('/users/holdings', HoldingController::class)->names('users.holdings');

        // IPO Management
        Route::apiResource('/ipo-details', IpoDetailController::class)->names('ipo-details');
        Route::apiResource('/ipo-applications', IpoApplicationController::class)->names('ipo-applications');

        // Sectors
        Route::apiResource('/sectors', SectorController::class)->names('sectors');

        // Portfolio
        Route::get('/portfolio', [PortfolioController::class, 'index'])->name('portfolio.index');

        Route::get('/dashboard', DashboardController::class);

        // Transaction
        Route::apiResource('/transactions', TransactionController::class)->names('transactions');

        // Watchlist
        Route::apiResource('/users/watchlists', WatchlistController::class);

        // Notifications
        Route::apiResource('/users/notifications', NotificationController::class);

    });
});

