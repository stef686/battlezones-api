<?php

use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Users\ConfirmEmailChangeController;
use App\Http\Controllers\Users\ConfirmPasswordChangeController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/email/verify/{user}/{hash}', [RegisterController::class, 'verifyEmail'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');

Route::get('/email/change/verify/{user}/{token}', ConfirmEmailChangeController::class)
    ->middleware(['signed', 'throttle:6,1'])
    ->name('email.change.verify');

Route::get('/password/change/confirm/{user}/{token}', ConfirmPasswordChangeController::class)
    ->middleware(['signed', 'throttle:6,1'])
    ->name('password.change.confirm');
