<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginTokenController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Users\MyProfileController;
use App\Http\Controllers\Users\UserProfileController;

Route::post('login/token', LoginTokenController::class)->name('login.token');
Route::post('register', RegisterController::class)->name('register');
Route::post('auth/resend-verification', [RegisterController::class, 'resendVerification']);
Route::post('auth/forgot-password', ForgotPasswordController::class)->name('password.email');
Route::post('auth/reset-password', ResetPasswordController::class)->name('password.update');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('profile', MyProfileController::class)->name('profile');
    Route::get('profile/{user}', UserProfileController::class)->name('profile.show');
});
