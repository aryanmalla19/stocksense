<?php

use App\Http\Controllers\SocialiteController;
use App\Http\Controllers\SseController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return 'welcome';
});

Route::get('/home', function () {
    return 'dashboard';
});

Route::view('/login', 'login')->name('login');

Route::controller(SocialiteController::class)->group(
    function(){
        Route::get('auth/google', 'googleLogin')->name('auth.google');
        Route::get('/auth/google/callback','googleAuthentication')->name('auth.google-callback');
    });

Route::get('/api/v1/auth/sse-notifications', [SseController::class, 'stream']);
Route::view('/sse-test', 'test');