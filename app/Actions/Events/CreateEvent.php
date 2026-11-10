<?php

namespace App\Actions\Events;

use App\Enums\EventOrganiserRole;
use App\Enums\EventStatus;
use App\Enums\PairingFormat;
use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Create an Event, whole, for the Organiser who will lead it.
 *
 * One transaction, so an Event never exists without a Lead to run it or a
 * scoring set to rank it on (ADR 0006). It is always a Draft: publishing is
 * a transition of its own. Swiss is the only pairing format built so far.
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
            $event = Event::query()->create([
                'pairing_format' => PairingFormat::Swiss,
                ...$attributes,
                'status' => EventStatus::Draft,
            ]);

            $event->organisers()->attach($creator, ['role' => EventOrganiserRole::Lead->value]);

            $this->replaceScoreTypes->execute($event, $scoreTypes);

            return $event;
        });
    }
}
