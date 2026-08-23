<?php

use App\Enums\ScheduleBlockType;
use App\Models\Event;
use App\Models\EventPoll;
use App\Models\EventScheduleBlock;
use App\Models\Round;
use Illuminate\Support\Collection;

/**
 * The schedule as a Player reads it, keyed by block label.
 *
 * @return Collection<string, array<string, mixed>>
 */
function scheduleBlocks(Event $event): Collection
{
    return collect(test()->getJson(route('events.schedule.index', ['event' => $event->slug]))
        ->assertSuccessful()
        ->json('data'))
        ->flatMap(fn (array $day): array => $day['blocks'])
        ->keyBy('label');
}

test('a round block is not live while its round is still a draft', function () {
    $event = Event::factory()->active()->create();
    $round = Round::factory()->for($event)->create(['number' => 1]);

    EventScheduleBlock::factory()->for($event)->round($round->id)->create(['label' => 'Round 1']);

    $block = scheduleBlocks($event)['Round 1'];

    expect($block['is_target_live'])->toBeFalse()
        ->and($block['target_id'])->toBe($round->id)
        ->and($block)->not->toHaveKey('url');
});

test('a round block flips live when its round publishes and back when it is unpublished', function () {
    $event = Event::factory()->active()->create();
    $organiser = organiserOf($event);
    $round = Round::factory()->for($event)->create(['number' => 1]);

    EventScheduleBlock::factory()->for($event)->round($round->id)->create(['label' => 'Round 1']);

    $this->actingAs($organiser)
        ->postJson(route('events.rounds.publish', ['event' => $event->slug, 'round' => $round->id]))
        ->assertSuccessful();

    expect(scheduleBlocks($event)['Round 1']['is_target_live'])->toBeTrue();

    $this->actingAs($organiser)
        ->deleteJson(route('events.rounds.unpublish', ['event' => $event->slug, 'round' => $round->id]))
        ->assertSuccessful();

    expect(scheduleBlocks($event)['Round 1']['is_target_live'])->toBeFalse();
});

test('a painting block is live only while the poll window is open', function () {
    $event = Event::factory()->active()->create();
    $organiser = organiserOf($event);
    $poll = EventPoll::factory()->for($event)->create();

    EventScheduleBlock::factory()->for($event)->paintingVoting()->create(['label' => 'Painting Voting']);

    expect(scheduleBlocks($event)['Painting Voting']['is_target_live'])->toBeFalse();

    $this->actingAs($organiser)
        ->postJson(route('events.polls.open', ['event' => $event->slug, 'poll' => $poll->id]))
        ->assertSuccessful();

    $live = scheduleBlocks($event)['Painting Voting'];

    expect($live['is_target_live'])->toBeTrue()
        ->and($live['target_id'])->toBe($poll->id);

    $this->actingAs($organiser)
        ->postJson(route('events.polls.close', ['event' => $event->slug, 'poll' => $poll->id]))
        ->assertSuccessful();

    expect(scheduleBlocks($event)['Painting Voting']['is_target_live'])->toBeFalse();
});

test('an info block is never live and carries no target', function () {
    $event = Event::factory()->active()->create();
    $round = Round::factory()->for($event)->live()->create(['number' => 1]);
    EventPoll::factory()->for($event)->open()->create();

    EventScheduleBlock::factory()->for($event)->create(['label' => 'Lunch']);

    $block = scheduleBlocks($event)['Lunch'];

    expect($block['type'])->toBe(ScheduleBlockType::Info->value)
        ->and($block['is_target_live'])->toBeFalse()
        ->and($block['target_id'])->toBeNull()
        ->and($round->isLive())->toBeTrue();
});

test('the schedule response carries no urls', function () {
    $event = Event::factory()->active()->create();
    $round = Round::factory()->for($event)->live()->create(['number' => 1]);
    EventScheduleBlock::factory()->for($event)->round($round->id)->create(['label' => 'Round 1']);

    $body = $this->getJson(route('events.schedule.index', ['event' => $event->slug]))
        ->assertSuccessful()
        ->content();

    expect($body)->not->toContain('http')
        ->and($body)->not->toContain('/rounds/');
});
