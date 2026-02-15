<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;

Route::post('login/token', [AuthenticatedSessionController::class, 'token']);
