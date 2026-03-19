<?php

use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Users\ConfirmEmailChangeController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/email/verify/{user}/{hash}', [RegisterController::class, 'verifyEmail'])
    ->middleware(['signed'])
    ->name('verification.verify');

Route::get('/email/change/verify/{user}/{token}', ConfirmEmailChangeController::class)
    ->middleware(['signed'])
    ->name('email.change.verify');
