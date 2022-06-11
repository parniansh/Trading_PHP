
<?php

use App\Http\Controllers\UserWalletController;
use Illuminate\Support\Facades\Route;


// Route::post('/add', [UserWalletController::class, 'add']);

Route::group(['prefix'=>'wallet'],function(){
        Route::post('/add', [UserWalletController::class, 'add']);
        Route::get('/getlist', [UserWalletController::class, 'getList']);
        Route::get('/getbyid', [UserWalletController::class, 'getById']);

    });