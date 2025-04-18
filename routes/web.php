<?php

use App\Http\Controllers\SocialiteController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return 'welcome';
});   

Route::get('/home', function () {
    return 'dashboard';
});   

//login blade file for testing/ delete later
Route::view('/login','login')->name('login');

Route::controller(SocialiteController::class)->group(
    function(){
        Route::get('auth/google', 'googleLogin')->name('auth.google');
        Route::get('/auth/google/callback','googleAuthentication')->name('auth.google-callback');  
    }
);

      


// Route::get('auth/facebook', [SocialiteController::class, 'facebookLogin'])->name('auth.facebook');