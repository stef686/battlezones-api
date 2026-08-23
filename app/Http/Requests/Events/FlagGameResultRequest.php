<?php

namespace App\Http\Requests\Events;

use App\Models\Event;
use App\Models\Game;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Knuckles\Scribe\Attributes\BodyParam;

#[BodyParam('reason', 'string', 'Why the result is wrong. Optional, but it is what an Organiser adjudicates on.', required: false, example: 'We agreed 85-70 the other way round.')]
class FlagGameResultRequest extends FormRequest
{
    /**
     * Only a Game's own Players may flag it, plus Organisers.
     *
     * Letting any Attendee flag invites a rival on the next table to generate
     * noise, and there is no story for moderating that.
     */
    public function authorize(): bool
    {
        $event = $this->event();

        return $event->isOrganisedBy($this->user())
            || $this->game()->attendees()
                ->whereHas('memberships', fn (Builder $query) => $query->where('user_id', $this->user()->getKey()))
                ->exists();
    }

    protected function prepareForValidation(): void
    {
        $event = $this->event();
        $game = $this->game();

        abort_unless($event->status->hasRoundsVisible(), 404);
        abort_unless($game->round->event_id === $event->getKey(), 404);

        abort_unless($game->hasResult(), 422, 'There is no result on this game to flag.');
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'reason' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ];
    }

    public function event(): Event
    {
        /** @var Event $event */
        $event = $this->route('event');

        return $event;
    }

    public function game(): Game
    {
        /** @var Game $game */
        $game = $this->route('game');

        return $game;
    }
}
