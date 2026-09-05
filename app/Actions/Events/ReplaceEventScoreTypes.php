<?php

namespace App\Actions\Events;

use App\Models\Event;
use App\Models\EventScoreType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

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

            foreach ($rows as $displayOrder => $row) {
                $scoreType = $existing->get((int) $row['id']);

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
                    'sort_direction' => $row['sort_direction'],
                    'is_derived' => $derived,
                    'is_primary' => (bool) $row['is_primary'],
                    'ranking_order' => $countsForRanking ? ++$rankingOrder : null,
                    'win_points' => $derived ? $row['win_points'] : null,
                    'draw_points' => $derived ? $row['draw_points'] : null,
                    'loss_points' => $derived ? $row['loss_points'] : null,
                    'display_order' => $displayOrder,
                ])->save();
            }
        });

        return $event->scoreTypes()->withExists('scores')->orderBy('display_order')->get();
    }
}
