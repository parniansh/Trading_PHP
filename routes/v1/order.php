<?php

use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

Route::post('/add-order', [OrderController::class, 'addOrder']);