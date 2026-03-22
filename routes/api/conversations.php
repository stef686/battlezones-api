<?php

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

Route::middleware('auth:sanctum')->group(function () {
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

    Route::post('conversations/{conversation}/messages', StoreMessageController::class)->name('conversations.messages.store')->middleware('throttle:30,1');
    Route::patch('conversations/{conversation}/messages/{message}', UpdateMessageController::class)->name('conversations.messages.update');
    Route::delete('conversations/{conversation}/messages/{message}', DeleteMessageController::class)->name('conversations.messages.destroy');
});
