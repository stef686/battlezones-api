<?php

namespace App\Actions\Events;

use App\Enums\EventOrganiserRole;
use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Create an Event, whole, for the Organiser who will lead it.
 *
 * One transaction, so an Event never exists without a Lead to run it or a
 * scoring set to rank it on (ADR 0006). It is always a Draft: publishing is
 * a transition of its own. The pairing format is the caller's to choose, from
 * the formats that are built; StoreEventRequest holds it to them.
 */
class CreateEvent
{
    public function __construct(private ReplaceEventScoreTypes $replaceScoreTypes) {}

    /**
     * @param  array<string, mixed>  $attributes
     * @param  list<array<string, mixed>>  $scoreTypes
     */
    public function execute(User $creator, array $attributes, array $scoreTypes): Event
    {
        return DB::transaction(function () use ($creator, $attributes, $scoreTypes): Event {
            $event = $this->createDraft($attributes);

            $event->organisers()->attach($creator, ['role' => EventOrganiserRole::Lead->value]);

            $this->replaceScoreTypes->execute($event, $scoreTypes);

            return $event;
        });
    }

    /**
     * The Event row itself. Validation checks the slug is free, but two
     * requests racing for the same one can both pass that check; the loser
     * hits the unique index, and is told the slug is taken rather than
     * answered with a 500.
     *
     * @param  array<string, mixed>  $attributes
     *
     * @throws ValidationException
     */
    private function createDraft(array $attributes): Event
    {
        try {
            return Event::query()->create([
                ...$attributes,
                'status' => EventStatus::Draft,
            ]);
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['slug' => 'That URL is already taken.']);
        }
    }
}
