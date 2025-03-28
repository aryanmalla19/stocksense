<?php

use App\Http\Controllers\SectorController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\StockPriceController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserSettingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::apiResource('/stocks', StockController::class);
Route::apiResource('/sectors', SectorController::class);
Route::apiResource('stock_prices', StockPriceController::class);
Route::apiResource('/user/settings', UserSettingController::class);

# User authentication route api
Route::post('login', [UserController::class, 'login']);
Route::get('/users', [UserController::class, 'getUsers']);

Route::group([
    'middleware' => 'api',
    'prefix' => 'user'
], function ($router) {
    Route::post('logout', [UserController::class, 'logout']);
    Route::post('refresh', [UserController::class, 'refresh']);
    Route::post('me', [UserController::class, 'me']);
});

