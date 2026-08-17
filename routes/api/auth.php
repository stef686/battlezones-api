<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginTokenController;
use App\Http\Controllers\Auth\RefreshTokenController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;

Route::middleware('throttle:auth')->group(function () {
    Route::post('login/token', LoginTokenController::class)->name('login.token');
    Route::post('register', RegisterController::class)->name('register');
});

Route::post('auth/refresh', RefreshTokenController::class)->name('auth.refresh')->middleware('throttle:60,1');

Route::post('auth/resend-verification', [RegisterController::class, 'resendVerification']);
Route::post('auth/forgot-password', ForgotPasswordController::class)->name('password.email');
Route::post('auth/reset-password', ResetPasswordController::class)->name('password.update');
