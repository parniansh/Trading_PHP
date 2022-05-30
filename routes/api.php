<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;

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
    Route::put('/', function () {
        return 500;
    });    
});
Route::group(['prefix'=>'v2'],function(){
    Route::get('/', function () {
        return 10000;
    });    
});
// Route::post('/login', [LoginController::class, 'login']);
Route::post('/auth/get-otp-code', [LoginController::class, 'otpCodeRequest']);
Route::post('/auth/token', [LoginController::class, 'OtpLogin']);
Route::middleware('auth:api')->post('logout', [LoginController::class, 'Logout']);

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
