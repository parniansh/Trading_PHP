<?php

use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;

Route::post('/register-user', [LoginController::class, 'registerNewUser']);
