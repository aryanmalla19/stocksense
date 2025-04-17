<?php

use App\Http\Controllers\SocialiteController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return 'welcome';
});   

Route::controller(SocialiteController::class)->group(
    function(){
        Route::get('auth/google', 'googleLogin')->name('auth.google');
        Route::get('/','googleAuthentication')->name('auth.google-callback');  
    }
);

      