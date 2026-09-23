<?php

namespace App\Actions\Events;

use App\Models\Event;
use App\Models\EventScoreType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Write an Event's whole scoring set in one transaction.
 *
 * Position in the payload is the display order, and position among the rows
 * that count for ranking is the ranking order — one array rather than a call
 * per row, so an Organiser reordering their columns cannot half-land and leave
 * the Standings ranked on an order nobody chose.
 */
class ReplaceEventScoreTypes
{
    /**
     * @param  list<array<string, mixed>>  $rows
     * @return Collection<int, EventScoreType>
     */
    public function execute(Event $event, array $rows): Collection
    {
        DB::transaction(function () use ($event, $rows): void {
            $existing = $event->scoreTypes()->get()->keyBy('id');
            $rankingOrder = 0;
            $kept = [];

            foreach ($rows as $displayOrder => $row) {
                $scoreType = isset($row['id'])
                    ? $existing->get((int) $row['id'])
                    : new EventScoreType([
                        'event_id' => $event->getKey(),
                        'slug' => $this->uniqueSlug($event, (string) $row['name']),
                    ]);

                if ($scoreType === null) {
                    continue;
                }

                $countsForRanking = (bool) $row['counts_for_ranking'];
                $derived = (bool) $row['is_derived'];

                // The slug is never taken from the payload: results are
                // submitted and the Standings sorted by slug, so a rename must
                // leave the Event's existing addresses alone.
                $scoreType->fill([
                    'name' => $row['name'],
                    'abbreviation' => $this->abbreviationOf($row),
                    'sort_direction' => $row['sort_direction'],
                    'is_derived' => $derived,
                    'is_primary' => (bool) $row['is_primary'],
                    'ranking_order' => $countsForRanking ? ++$rankingOrder : null,
                    'win_points' => $derived ? $row['win_points'] : null,
                    'draw_points' => $derived ? $row['draw_points'] : null,
                    'loss_points' => $derived ? $row['loss_points'] : null,
                    'display_order' => $displayOrder,
                ])->save();

                $kept[] = $scoreType->getKey();
            }

            // Whatever the Organiser left out is gone. A column Games have
            // been scored on never reaches here: the request refuses to drop
            // one, because its scores would cascade away with it.
            $event->scoreTypes()->whereNotIn('id', $kept)->delete();
        });

        return $event->scoreTypes()->withExists('scores')->orderBy('display_order')->get();
    }

    /**
     * The heading the column is shown under.
     *
     * An Organiser's own where they wrote one, and the initials of the name
     * otherwise — a column has to have a heading, and asking every Organiser
     * to invent one for Victory Points would be asking them to type VP.
     *
     * @param  array<string, mixed>  $row
     */
    private function abbreviationOf(array $row): string
    {
        $written = trim((string) ($row['abbreviation'] ?? ''));

        return $written !== '' ? $written : EventScoreType::abbreviate((string) $row['name']);
    }

    /**
     * A slug of its own, derived from the name and unique within the Event.
     *
     * Never taken from the client: a Score Type is addressed by slug when a
     * result is submitted and when the Standings are sorted, so the platform
     * owns the address and the Organiser owns the name.
     */
    private function uniqueSlug(Event $event, string $name): string
    {
        $base = Str::slug($name) ?: 'score';
        $slug = $base;
        $suffix = 1;

        while ($event->scoreTypes()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.++$suffix;
        }

        return $slug;
    }
}
