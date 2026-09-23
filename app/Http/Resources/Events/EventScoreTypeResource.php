<?php

namespace App\Http\Resources\Events;

use App\Models\EventScoreType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A Score Type as the Organiser who set it up sees it.
 *
 * Fuller than the columns a Round or a Game carries: those say only what a
 * Player needs to read a score, while an Organiser editing the scoring needs
 * the points behind a derived column and whether anything has been scored
 * under it yet.
 *
 * @mixin EventScoreType
 */
class EventScoreTypeResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            // The heading it is shown under on a Game and in the Standings.
            'abbreviation' => $this->abbreviation,
            // Server-owned: results are submitted against a slug, so it
            // survives a rename rather than travelling with the name.
            'slug' => $this->slug,
            'sort_direction' => $this->sort_direction->value,
            // Whether Players enter this column or the system works it out
            // from the result, which is what decides who may submit it.
            'is_derived' => $this->is_derived,
            'is_primary' => $this->is_primary,
            // Said as a yes or no rather than as the raw order: the position
            // among the ranking columns is the row's own place in the list.
            'counts_for_ranking' => $this->ranking_order !== null,
            'ranking_order' => $this->ranking_order,
            'win_points' => $this->win_points,
            'draw_points' => $this->draw_points,
            'loss_points' => $this->loss_points,
            'display_order' => $this->display_order,
            // Whether any Game has been scored under this column. Scores
            // cascade on delete, so this is what a screen locks a row on.
            'is_scored' => $this->scores_exists ?? false,
        ];
    }
}
