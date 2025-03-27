<?php

use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


# User authentication route api
Route::post('login', [UserController::class, 'login']);

Route::group([
    'middleware' => 'api',
    'prefix' => 'user'
], function () {
    Route::post('logout', [UserController::class, 'logout']);
    Route::post('refresh', [UserController::class, 'refresh']);
    Route::post('me', [UserController::class, 'me']);
});



