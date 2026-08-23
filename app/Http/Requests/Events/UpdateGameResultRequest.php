<?php

namespace App\Http\Requests\Events;

use Knuckles\Scribe\Attributes\BodyParam;

#[BodyParam('scores', 'object', 'Scores keyed by Attendee id, then by Score Type slug. Every Attendee in the Game must be present, and derived Score Types are rejected.', required: true, example: ['1' => ['victory-points' => 85], '2' => ['victory-points' => 70]])]
class UpdateGameResultRequest extends GameResultRequest
{
    protected function prepareForValidation(): void
    {
        abort_unless($this->game()->round->event_id === $this->event()->getKey(), 404);
    }

    /**
     * Organisers correct results at any point, in any Round.
     *
     * An Organiser who is also competing may correct their own Game: blocking
     * it sounds principled, but leaves the only person present with the power
     * to fix a wrong score unable to use it. The edit is attributed instead.
     */
    public function authorize(): bool
    {
        return $this->event()->isOrganisedBy($this->user());
    }
}
