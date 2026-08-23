<?php

use App\Http\Controllers\Events\ClaimInviteController;
use App\Http\Controllers\Events\CloseEventPollController;
use App\Http\Controllers\Events\DeleteAttendeeMemberController;
use App\Http\Controllers\Events\DeleteEventOrganiserController;
use App\Http\Controllers\Events\DeleteEventScheduleBlockController;
use App\Http\Controllers\Events\ExportEventFeedbackController;
use App\Http\Controllers\Events\FlagGameResultController;
use App\Http\Controllers\Events\GenerateRoundController;
use App\Http\Controllers\Events\ListEventAttendeesController;
use App\Http\Controllers\Events\ListEventFlaggedResultsController;
use App\Http\Controllers\Events\ListEventGalleryController;
use App\Http\Controllers\Events\ListEventOrganisersController;
use App\Http\Controllers\Events\ListEventPollsController;
use App\Http\Controllers\Events\ListEventRoundsController;
use App\Http\Controllers\Events\ListEventScheduleController;
use App\Http\Controllers\Events\ListEventsController;
use App\Http\Controllers\Events\ListEventStandingsController;
use App\Http\Controllers\Events\ListEventUpdatesController;
use App\Http\Controllers\Events\ListPollCandidatesController;
use App\Http\Controllers\Events\OpenEventPollController;
use App\Http\Controllers\Events\PublishRoundController;
use App\Http\Controllers\Events\ReorderEventScheduleController;
use App\Http\Controllers\Events\ReplaceBallotController;
use App\Http\Controllers\Events\ResolveGameResultFlagController;
use App\Http\Controllers\Events\RevealArmyListsController;
use App\Http\Controllers\Events\SendEventFeedbackRequestsController;
use App\Http\Controllers\Events\ShowEventAttendeeController;
use App\Http\Controllers\Events\ShowEventController;
use App\Http\Controllers\Events\ShowEventFeedbackController;
use App\Http\Controllers\Events\ShowEventGameController;
use App\Http\Controllers\Events\ShowEventPollResultsController;
use App\Http\Controllers\Events\ShowEventRoundController;
use App\Http\Controllers\Events\ShowFeedbackFormController;
use App\Http\Controllers\Events\ShowInviteController;
use App\Http\Controllers\Events\ShowMyGameController;
use App\Http\Controllers\Events\StoreAttendeeMemberController;
use App\Http\Controllers\Events\StoreEventAttendeeController;
use App\Http\Controllers\Events\StoreEventInviteController;
use App\Http\Controllers\Events\StoreEventOrganiserController;
use App\Http\Controllers\Events\StoreEventPollController;
use App\Http\Controllers\Events\StoreEventScheduleBlockController;
use App\Http\Controllers\Events\StoreGameResultController;
use App\Http\Controllers\Events\StoreInviteSessionController;
use App\Http\Controllers\Events\SubmitFeedbackController;
use App\Http\Controllers\Events\SwapRoundPairingsController;
use App\Http\Controllers\Events\UnlockArmyListController;
use App\Http\Controllers\Events\UnpublishRoundController;
use App\Http\Controllers\Events\UpdateArmyListController;
use App\Http\Controllers\Events\UpdateEventAttendeeController;
use App\Http\Controllers\Events\UpdateEventScheduleBlockController;
use App\Http\Controllers\Events\UpdateGameResultController;
use App\Http\Controllers\Events\UpdatePaintingEntryController;

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
Route::get('events/{event:slug}/schedule', ListEventScheduleController::class)->name('events.schedule.index');

// Budgeted per token rather than per address: a hall full of Players sharing
// one venue IP must not share one budget. See AppServiceProvider.
Route::get('feedback/{token}', ShowFeedbackFormController::class)->name('feedback.show')
    ->middleware('throttle:token-read');
Route::post('feedback/{token}', SubmitFeedbackController::class)->name('feedback.store')
    ->middleware('throttle:token-write');

Route::get('invites/{token}', ShowInviteController::class)->name('invites.show')
    ->middleware('throttle:token-read');
Route::post('invites/{token}/session', StoreInviteSessionController::class)->name('invites.session')
    ->middleware('throttle:token-write');
Route::post('invites/{token}/claim', ClaimInviteController::class)->name('invites.claim')
    ->middleware('throttle:token-write');

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('events/{event:slug}/organisers', ListEventOrganisersController::class)
        ->name('events.organisers.index');
    Route::post('events/{event:slug}/organisers', StoreEventOrganiserController::class)
        ->name('events.organisers.store');
    Route::delete('events/{event:slug}/organisers/{user}', DeleteEventOrganiserController::class)
        ->name('events.organisers.destroy');

    Route::post('events/{event:slug}/invites', StoreEventInviteController::class)
        ->name('events.invites.store');

    Route::post('events/{event:slug}/feedback/requests', SendEventFeedbackRequestsController::class)
        ->name('events.feedback.invite');
    Route::get('events/{event:slug}/feedback', ShowEventFeedbackController::class)
        ->name('events.feedback.index');
    Route::get('events/{event:slug}/feedback/export', ExportEventFeedbackController::class)
        ->name('events.feedback.export');

    Route::get('events/{event:slug}/polls', ListEventPollsController::class)
        ->name('events.polls.index');
    Route::post('events/{event:slug}/polls', StoreEventPollController::class)
        ->name('events.polls.store');
    Route::scopeBindings()->post('events/{event:slug}/polls/{poll}/open', OpenEventPollController::class)
        ->name('events.polls.open');
    Route::scopeBindings()->get('events/{event:slug}/polls/{poll}/candidates', ListPollCandidatesController::class)
        ->name('events.polls.candidates');
    Route::scopeBindings()->get('events/{event:slug}/polls/{poll}/results', ShowEventPollResultsController::class)
        ->name('events.polls.results');
    Route::scopeBindings()->post('events/{event:slug}/polls/{poll}/close', CloseEventPollController::class)
        ->name('events.polls.close');

    Route::scopeBindings()->put('events/{event:slug}/polls/{poll}/ballot', ReplaceBallotController::class)
        ->name('events.polls.ballot.update');

    Route::scopeBindings()->patch('events/{event:slug}/attendees/{attendee}/painting', UpdatePaintingEntryController::class)
        ->name('events.attendees.painting.update');

    Route::post('events/{event:slug}/schedule', StoreEventScheduleBlockController::class)
        ->name('events.schedule.store');
    Route::post('events/{event:slug}/schedule/reorder', ReorderEventScheduleController::class)
        ->name('events.schedule.reorder');
    Route::patch('events/{event:slug}/schedule/{block}', UpdateEventScheduleBlockController::class)
        ->name('events.schedule.update');
    Route::delete('events/{event:slug}/schedule/{block}', DeleteEventScheduleBlockController::class)
        ->name('events.schedule.destroy');

    Route::get('events/{event:slug}/my-game', ShowMyGameController::class)
        ->name('events.my-game.show');

    Route::post('events/{event:slug}/games/{game}/result', StoreGameResultController::class)
        ->name('events.games.result.store');
    Route::put('events/{event:slug}/games/{game}/result', UpdateGameResultController::class)
        ->name('events.games.result.update');
    Route::post('events/{event:slug}/games/{game}/flag', FlagGameResultController::class)
        ->name('events.games.flag.store');
    Route::post('events/{event:slug}/games/{game}/flag/resolve', ResolveGameResultFlagController::class)
        ->name('events.games.flag.resolve');
    Route::get('events/{event:slug}/flags', ListEventFlaggedResultsController::class)
        ->name('events.flags.index');

    Route::post('events/{event:slug}/rounds', GenerateRoundController::class)
        ->name('events.rounds.generate');
    Route::scopeBindings()->post('events/{event:slug}/rounds/{round}/publish', PublishRoundController::class)
        ->name('events.rounds.publish');
    Route::scopeBindings()->post('events/{event:slug}/rounds/{round}/swap', SwapRoundPairingsController::class)
        ->name('events.rounds.swap');
    Route::scopeBindings()->delete('events/{event:slug}/rounds/{round}/publish', UnpublishRoundController::class)
        ->name('events.rounds.unpublish');

    Route::put('events/{event:slug}/army-list', UpdateArmyListController::class)
        ->name('events.army-list.update');

    Route::scopeBindings()->post('events/{event:slug}/attendees/{attendee}/army-lists/reveal', RevealArmyListsController::class)
        ->name('events.army-lists.reveal');
    Route::scopeBindings()->post('events/{event:slug}/attendees/{attendee}/members/{member}/army-list/unlock', UnlockArmyListController::class)
        ->name('events.army-list.unlock');

    Route::post('events/{event:slug}/attendees', StoreEventAttendeeController::class)
        ->name('events.attendees.store');
    Route::scopeBindings()->patch('events/{event:slug}/attendees/{attendee}', UpdateEventAttendeeController::class)
        ->name('events.attendees.update');
    Route::scopeBindings()->post('events/{event:slug}/attendees/{attendee}/members', StoreAttendeeMemberController::class)
        ->name('events.attendees.members.store');
    Route::scopeBindings()->delete('events/{event:slug}/attendees/{attendee}/members/{member}', DeleteAttendeeMemberController::class)
        ->name('events.attendees.members.destroy');
});
