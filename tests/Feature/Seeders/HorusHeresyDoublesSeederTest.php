<?php

use App\Enums\Allegiance;
use App\Enums\EventStatus;
use App\Enums\PollType;
use App\Enums\RegistrationMode;
use App\Enums\RoundStatus;
use App\Models\Event;
use App\Models\EventAttendee;
use App\Models\EventPoll;
use App\Models\EventScoreType;
use Database\Seeders\HorusHeresyDoublesSeeder;

function seededEvent(): Event
{
    test()->seed(HorusHeresyDoublesSeeder::class);

    return Event::query()->where('slug', HorusHeresyDoublesSeeder::SLUG)->sole();
}

test('it seeds the event with its configuration and settings', function () {
    $event = seededEvent();

    expect($event->name)->toBe('Horus Heresy Doubles')
        ->and($event->timezone)->toBe('Europe/London')
        ->and($event->attendee_size)->toBe(2)
        ->and($event->registration_mode)->toBe(RegistrationMode::Open)
        ->and($event->settings->roundCount)->toBe(5)
        ->and($event->settings->requiresOpposedAllegiance)->toBeTrue()
        ->and($event->gameSystem->slug)->toBe('horus-heresy');
});

test('it seeds match points as derived and ranked ahead of victory points', function () {
    $event = seededEvent();

    $matchPoints = $event->scoreTypes()->where('slug', 'match-points')->sole();
    $victoryPoints = $event->scoreTypes()->where('slug', 'victory-points')->sole();

    expect($matchPoints->is_derived)->toBeTrue()
        ->and($matchPoints->ranking_order)->toBe(1)
        ->and($matchPoints->win_points)->toBe('3.00')
        ->and($matchPoints->draw_points)->toBe('1.00')
        ->and($matchPoints->loss_points)->toBe('0.00')
        ->and($victoryPoints->is_derived)->toBeFalse()
        ->and($victoryPoints->ranking_order)->toBe(2);
});

test('it seeds both polls closed for organisers to open on the day', function () {
    $event = seededEvent();

    $polls = $event->polls()->get()->keyBy(fn (EventPoll $poll): string => $poll->type->value);

    expect($polls)->toHaveCount(2)
        ->and($polls[PollType::Painting->value]->isOpen())->toBeFalse()
        ->and($polls[PollType::FavouriteOpponent->value]->isOpen())->toBeFalse()
        ->and($polls[PollType::Painting->value]->votes_per_player)->toBe(3);
});

test('running the seeder twice does not duplicate anything', function () {
    seededEvent();
    seededEvent();

    expect(Event::query()->where('slug', HorusHeresyDoublesSeeder::SLUG)->count())->toBe(1)
        ->and(EventScoreType::query()->count())->toBe(2)
        ->and(EventPoll::query()->count())->toBe(2);
});

test('it seeds no attendees, games or scores', function () {
    $event = seededEvent();

    expect($event->attendees()->count())->toBe(0)
        ->and($event->rounds()->count())->toBe(0);
});

test('the seeded event is complete enough to pair round one', function () {
    $event = seededEvent();
    $event->update(['status' => EventStatus::Active]);

    foreach ([Allegiance::Loyalist, Allegiance::Traitor] as $allegiance) {
        EventAttendee::factory()->count(2)->for($event)->withMember()->create(['allegiance' => $allegiance]);
    }

    $round = generatePairings($event->fresh());

    expect($round->status)->toBe(RoundStatus::Draft)
        ->and($round->number)->toBe(1)
        ->and($round->games()->count())->toBe(2);

    foreach ($round->games as $game) {
        expect($game->attendees->pluck('allegiance')->unique())->toHaveCount(2);
    }
});
