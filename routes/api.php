<?php

use App\Http\Controllers\Auth\LoginTokenController;
use App\Http\Controllers\Auth\RegisterController;

Route::post('login/token', LoginTokenController::class)->name('login.token');
Route::post('register', RegisterController::class)->name('register');
