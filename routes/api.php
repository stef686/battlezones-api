<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginTokenController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Conversations\AddGroupMembersController;
use App\Http\Controllers\Conversations\ArchiveConversationController;
use App\Http\Controllers\Conversations\DeleteConversationController;
use App\Http\Controllers\Conversations\DeleteMessageController;
use App\Http\Controllers\Conversations\LeaveGroupConversationController;
use App\Http\Controllers\Conversations\ListConversationsController;
use App\Http\Controllers\Conversations\ReadConversationController;
use App\Http\Controllers\Conversations\ShowConversationController;
use App\Http\Controllers\Conversations\StartConversationController;
use App\Http\Controllers\Conversations\StartGroupConversationController;
use App\Http\Controllers\Conversations\StoreMessageController;
use App\Http\Controllers\Conversations\UnarchiveConversationController;
use App\Http\Controllers\Conversations\UpdateGroupNameController;
use App\Http\Controllers\Conversations\UpdateMessageController;
use App\Http\Controllers\Gallery\DeletePhotoController;
use App\Http\Controllers\Gallery\ListPhotosController;
use App\Http\Controllers\Gallery\ShowPhotoController;
use App\Http\Controllers\Gallery\StorePhotoController;
use App\Http\Controllers\Gallery\UpdatePhotoController;
use App\Http\Controllers\Gallery\UserGalleryController;
use App\Http\Controllers\Reactions\ToggleReactionController;
use App\Http\Controllers\Users\ChangeEmailController;
use App\Http\Controllers\Users\ChangePasswordController;
use App\Http\Controllers\Users\FollowUserController;
use App\Http\Controllers\Users\GetNotificationSettingsController;
use App\Http\Controllers\Users\MyProfileController;
use App\Http\Controllers\Users\SearchUsersController;
use App\Http\Controllers\Users\UnfollowUserController;
use App\Http\Controllers\Users\UpdateNotificationSettingsController;
use App\Http\Controllers\Users\UpdateProfileController;
use App\Http\Controllers\Users\UserProfileController;

Route::post('login/token', LoginTokenController::class)->name('login.token');
Route::post('register', RegisterController::class)->name('register');
Route::post('auth/resend-verification', [RegisterController::class, 'resendVerification']);
Route::post('auth/forgot-password', ForgotPasswordController::class)->name('password.email');
Route::post('auth/reset-password', ResetPasswordController::class)->name('password.update');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('profile', MyProfileController::class)->name('profile');
    Route::patch('profile', UpdateProfileController::class)->name('profile.update');
    Route::post('profile/email', ChangeEmailController::class)->name('profile.email');
    Route::post('profile/password', ChangePasswordController::class)->name('profile.password');
    Route::get('profile/{user}', UserProfileController::class)->name('profile.show');
    Route::get('notification-settings', GetNotificationSettingsController::class)->name('notification-settings');
    Route::patch('notification-settings', UpdateNotificationSettingsController::class)->name('notification-settings.update');

    // Gallery
    Route::get('gallery', ListPhotosController::class)->name('gallery.index');
    Route::post('gallery', StorePhotoController::class)->name('gallery.store');
    Route::get('gallery/{photo}', ShowPhotoController::class)->name('gallery.show');
    Route::patch('gallery/{photo}', UpdatePhotoController::class)->name('gallery.update');
    Route::delete('gallery/{photo}', DeletePhotoController::class)->name('gallery.destroy');
    Route::post('gallery/{photo}/react', ToggleReactionController::class)->name('gallery.react');

    // Public galleries
    Route::get('users/{user}/gallery', UserGalleryController::class)->name('users.gallery');

    // User search
    Route::get('users/search', SearchUsersController::class)->name('users.search');

    // Follow
    Route::post('users/{user}/follow', FollowUserController::class)->name('users.follow');
    Route::delete('users/{user}/follow', UnfollowUserController::class)->name('users.unfollow');

    // Conversations
    Route::get('conversations', ListConversationsController::class)->name('conversations.index');
    Route::post('conversations', StartConversationController::class)->name('conversations.store')->middleware('throttle:30,1');
    Route::post('conversations/group', StartGroupConversationController::class)->name('conversations.group.store')->middleware('throttle:30,1');
    Route::get('conversations/{conversation}', ShowConversationController::class)->name('conversations.show');
    Route::post('conversations/{conversation}/members', AddGroupMembersController::class)->name('conversations.members.store');
    Route::post('conversations/{conversation}/leave', LeaveGroupConversationController::class)->name('conversations.leave');
    Route::patch('conversations/{conversation}/name', UpdateGroupNameController::class)->name('conversations.name.update');
    Route::delete('conversations/{conversation}', DeleteConversationController::class)->name('conversations.destroy');
    Route::post('conversations/{conversation}/archive', ArchiveConversationController::class)->name('conversations.archive');
    Route::post('conversations/{conversation}/unarchive', UnarchiveConversationController::class)->name('conversations.unarchive');
    Route::post('conversations/{conversation}/read', ReadConversationController::class)->name('conversations.read');

    // Messages
    Route::post('conversations/{conversation}/messages', StoreMessageController::class)->name('conversations.messages.store')->middleware('throttle:30,1');
    Route::patch('conversations/{conversation}/messages/{message}', UpdateMessageController::class)->name('conversations.messages.update');
    Route::delete('conversations/{conversation}/messages/{message}', DeleteMessageController::class)->name('conversations.messages.destroy');
});
