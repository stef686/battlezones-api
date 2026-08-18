<?php

use App\Http\Controllers\Events\ClaimInviteController;
use App\Http\Controllers\Events\DeleteEventOrganiserController;
use App\Http\Controllers\Events\ListEventAttendeesController;
use App\Http\Controllers\Events\ListEventGalleryController;
use App\Http\Controllers\Events\ListEventOrganisersController;
use App\Http\Controllers\Events\ListEventRoundsController;
use App\Http\Controllers\Events\ListEventsController;
use App\Http\Controllers\Events\ListEventStandingsController;
use App\Http\Controllers\Events\ListEventUpdatesController;
use App\Http\Controllers\Events\ShowEventAttendeeController;
use App\Http\Controllers\Events\ShowEventController;
use App\Http\Controllers\Events\ShowEventGameController;
use App\Http\Controllers\Events\ShowEventRoundController;
use App\Http\Controllers\Events\ShowInviteController;
use App\Http\Controllers\Events\StoreEventInviteController;
use App\Http\Controllers\Events\StoreEventOrganiserController;
use App\Http\Controllers\Events\StoreInviteSessionController;

Route::get('events', ListEventsController::class)->name('events.index');
Route::get('events/{event:slug}', ShowEventController::class)->name('events.show');
Route::get('events/{event:slug}/updates', ListEventUpdatesController::class)->name('events.updates.index');
Route::get('events/{event:slug}/attendees', ListEventAttendeesController::class)->name('events.attendees.index');
Route::scopeBindings()->get('events/{event:slug}/attendees/{attendee}', ShowEventAttendeeController::class)
    ->name('events.attendees.show');
Route::get('events/{event:slug}/rounds', ListEventRoundsController::class)->name('events.rounds.index');
Route::scopeBindings()->get('events/{event:slug}/rounds/{round}', ShowEventRoundController::class)
    ->name('events.rounds.show');
Route::get('events/{event:slug}/games/{game}', ShowEventGameController::class)->name('events.games.show');
Route::get('events/{event:slug}/standings', ListEventStandingsController::class)->name('events.standings.index');
Route::get('events/{event:slug}/gallery', ListEventGalleryController::class)->name('events.gallery.index');

Route::get('invites/{token}', ShowInviteController::class)->name('invites.show')
    ->middleware('throttle:30,1');
Route::post('invites/{token}/session', StoreInviteSessionController::class)->name('invites.session')
    ->middleware('throttle:auth');
Route::post('invites/{token}/claim', ClaimInviteController::class)->name('invites.claim')
    ->middleware('throttle:auth');

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('events/{event:slug}/organisers', ListEventOrganisersController::class)
        ->name('events.organisers.index');
    Route::post('events/{event:slug}/organisers', StoreEventOrganiserController::class)
        ->name('events.organisers.store');
    Route::delete('events/{event:slug}/organisers/{user}', DeleteEventOrganiserController::class)
        ->name('events.organisers.destroy');

    Route::post('events/{event:slug}/invites', StoreEventInviteController::class)
        ->name('events.invites.store');
});
