<?php

namespace App\Http\Requests\Events;

use Illuminate\Database\Eloquent\Builder;
use Knuckles\Scribe\Attributes\BodyParam;

#[BodyParam('scores', 'object', 'Scores keyed by Attendee id, then by Score Type slug. Every Attendee in the Game must be present, and derived Score Types are rejected.', required: true, example: ['1' => ['victory-points' => 85], '2' => ['victory-points' => 70]])]
class SubmitGameResultRequest extends GameResultRequest
{
    /**
     * A Game only exists to its Players once its Round is Live, so anything
     * earlier is hidden rather than refused.
     */
    protected function prepareForValidation(): void
    {
        $event = $this->event();
        $game = $this->game();

        abort_unless($event->status->hasRoundsVisible(), 404);
        abort_unless($game->round->event_id === $event->getKey(), 404);
        abort_unless($game->round->isLive(), 404);

        abort_if($game->is_bye, 403, 'A bye has no result to submit.');
    }

    public function authorize(): bool
    {
        return $this->game()->attendees()
            ->whereHas('memberships', fn (Builder $query) => $query->where('user_id', $this->user()->getKey()))
            ->exists();
    }
}
