<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\SectorController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\StockPriceController;
use App\Http\Controllers\UserSettingController;
use Illuminate\Support\Facades\Route;

Route::apiResource('/stocks', StockController::class);
Route::apiResource('/sectors', SectorController::class);
Route::apiResource('stock_prices', StockPriceController::class);
Route::apiResource('/user/settings', UserSettingController::class);

// User authentication route api
Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);

Route::group([
    'middleware' => 'auth:api',
    'prefix' => 'auth',
], function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('me', [AuthController::class, 'me']);
});
