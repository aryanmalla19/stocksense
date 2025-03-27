<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

# User authentication route api
Route::post('login', [AuthController::class, 'login']);

Route::group([
    'middleware' => 'auth:api',
    'prefix' => 'user'
], function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('me', [AuthController::class, 'me']);
});



