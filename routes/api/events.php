<?php

use App\Http\Controllers\Events\ListEventAttendeesController;
use App\Http\Controllers\Events\ListEventRoundsController;
use App\Http\Controllers\Events\ListEventsController;
use App\Http\Controllers\Events\ListEventStandingsController;
use App\Http\Controllers\Events\ListEventUpdatesController;
use App\Http\Controllers\Events\ShowEventAttendeeController;
use App\Http\Controllers\Events\ShowEventController;
use App\Http\Controllers\Events\ShowEventGameController;
use App\Http\Controllers\Events\ShowEventRoundController;

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
