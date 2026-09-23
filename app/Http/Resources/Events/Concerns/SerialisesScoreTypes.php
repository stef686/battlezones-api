<?php

namespace App\Http\Resources\Events\Concerns;

use App\Models\Event;
use App\Models\EventScoreType;
use Illuminate\Database\Eloquent\Collection;

trait SerialisesScoreTypes
{
    /**
     * The columns an Event is scored on, in the order an Organiser set them.
     *
     * @return Collection<int, EventScoreType>
     */
    protected function orderedScoreTypes(Event $event): Collection
    {
        return $event->scoreTypes->sortBy('display_order')->values();
    }

    /**
     * The columns a Game is scored on, sent whether or not a result has
     * landed. Read from the Event rather than inferred from the scores in
     * hand, so a Game nobody has played yet shows what it is waiting for
     * instead of collapsing to nothing.
     *
     * @param  Collection<int, EventScoreType>  $ordered
     * @return list<array<string, mixed>>
     */
    protected function serialiseScoreTypes(Collection $ordered, ?EventScoreType $primary): array
    {
        return $ordered->map(fn (EventScoreType $type): array => [
            'slug' => $type->slug,
            'name' => $type->name,
            // What a table heading shows, since a column of numbers has no
            // room for "Victory Points". Sent rather than derived, so every
            // client shows the heading the Organiser chose.
            'abbreviation' => $type->abbreviation,
            // Which column a Game listing leads with, where it has room for
            // one. Resolved here rather than sent raw, so exactly one column
            // is marked however the Event was set up and a client never has
            // to pick when nobody has. Everything else is read on the Game.
            'is_primary' => $type->id === $primary?->id,
        ])->all();
    }
}
