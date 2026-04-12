<?php

use App\Http\Controllers\Events\ListEventsController;
use App\Http\Controllers\Events\ShowEventController;

Route::get('events', ListEventsController::class)->name('events.index');
Route::get('events/{event:slug}', ShowEventController::class)->name('events.show');
