<?php

use App\Http\Controllers\Events\ListEventsController;
use App\Http\Controllers\Events\ListEventUpdatesController;
use App\Http\Controllers\Events\ShowEventController;

Route::get('events', ListEventsController::class)->name('events.index');
Route::get('events/{event:slug}', ShowEventController::class)->name('events.show');
Route::get('events/{event:slug}/updates', ListEventUpdatesController::class)->name('events.updates.index');
