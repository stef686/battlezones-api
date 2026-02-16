<?php

use App\Http\Controllers\Auth\LoginTokenController;

Route::post('login/token', LoginTokenController::class)->name('login.token');
