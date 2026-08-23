<?php

use App\Http\Controllers\Users\BlockUserController;
use App\Http\Controllers\Users\ChangeEmailController;
use App\Http\Controllers\Users\ChangePasswordController;
use App\Http\Controllers\Users\FollowUserController;
use App\Http\Controllers\Users\GetNotificationSettingsController;
use App\Http\Controllers\Users\GetPrivacySettingsController;
use App\Http\Controllers\Users\ListBlockedUsersController;
use App\Http\Controllers\Users\ListFollowersController;
use App\Http\Controllers\Users\ListFollowingController;
use App\Http\Controllers\Users\ListNotificationsController;
use App\Http\Controllers\Users\MarkAllNotificationsReadController;
use App\Http\Controllers\Users\MarkNotificationReadController;
use App\Http\Controllers\Users\MyProfileController;
use App\Http\Controllers\Users\SearchUsersController;
use App\Http\Controllers\Users\UnblockUserController;
use App\Http\Controllers\Users\UnfollowUserController;
use App\Http\Controllers\Users\UpdateNotificationSettingsController;
use App\Http\Controllers\Users\UpdatePrivacySettingsController;
use App\Http\Controllers\Users\UpdateProfileController;
use App\Http\Controllers\Users\UserProfileController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('profile', MyProfileController::class)->name('profile');
    Route::patch('profile', UpdateProfileController::class)->name('profile.update');
    Route::post('profile/email', ChangeEmailController::class)->name('profile.email');
    Route::post('profile/password', ChangePasswordController::class)->name('profile.password');
    Route::get('profile/{user}', UserProfileController::class)->name('profile.show')->middleware('throttle:30,1');
    Route::get('notifications', ListNotificationsController::class)->name('notifications.index');
    Route::post('notifications/read', MarkAllNotificationsReadController::class)->name('notifications.read-all');
    Route::post('notifications/{notification}/read', MarkNotificationReadController::class)->name('notifications.read');

    Route::get('notification-settings', GetNotificationSettingsController::class)->name('notification-settings');
    Route::patch('notification-settings', UpdateNotificationSettingsController::class)->name('notification-settings.update');
    Route::get('privacy-settings', GetPrivacySettingsController::class)->name('privacy-settings');
    Route::patch('privacy-settings', UpdatePrivacySettingsController::class)->name('privacy-settings.update');

    Route::get('users/search', SearchUsersController::class)->name('users.search')->middleware('throttle:30,1');

    Route::post('users/{user}/follow', FollowUserController::class)->name('users.follow');
    Route::delete('users/{user}/follow', UnfollowUserController::class)->name('users.unfollow');
    Route::get('users/{user}/followers', ListFollowersController::class)->name('users.followers')->middleware('throttle:30,1');
    Route::get('users/{user}/following', ListFollowingController::class)->name('users.following')->middleware('throttle:30,1');

    Route::post('users/{user}/block', BlockUserController::class)->name('users.block');
    Route::delete('users/{user}/block', UnblockUserController::class)->name('users.unblock');
    Route::get('blocked-users', ListBlockedUsersController::class)->name('blocked-users.index')->middleware('throttle:30,1');
});
