<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ReferralController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::group(['prefix'=>'v1'],function(){
    Route::group(['prefix'=>'auth'],function(){
        Route::post('/get-otp-code', [LoginController::class, 'otpCodeRequest']);
        Route::post('/token', [LoginController::class, 'OtpLogin']);

    
    });
    Route::group([ 'middleware' => 'auth:api' ], function() {
        foreach (glob(__DIR__.'/v1/*.php') as $fileName){
            include_once $fileName;
        }
    });
});
