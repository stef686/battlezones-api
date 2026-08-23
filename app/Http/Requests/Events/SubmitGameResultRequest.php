<?php

namespace App\Http\Requests\Events;

use App\Models\Event;
use App\Models\EventScoreType;
use App\Models\Game;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;
use Knuckles\Scribe\Attributes\BodyParam;

#[BodyParam('scores', 'object', 'Scores keyed by Attendee id, then by Score Type slug. Every Attendee in the Game must be present, and derived Score Types are rejected.', required: true, example: ['1' => ['victory-points' => 85], '2' => ['victory-points' => 70]])]
class SubmitGameResultRequest extends FormRequest
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

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'scores' => ['required', 'array'],
            'scores.*' => ['required', 'array'],
            'scores.*.*' => ['required', 'numeric'],
        ];
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            fn (Validator $validator) => $this->validateScores($validator),
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

    /**
     * The Score Types the submitted slugs resolve to, keyed by slug.
     *
     * @return Collection<string, EventScoreType>
     */
    public function scoreTypes(): Collection
    {
        return $this->event()->scoreTypes()->get()->keyBy('slug');
    }

    /**
     * A result is scored for the whole Game, so a partial submission is a
     * half-recorded Game rather than a smaller version of a valid one.
     */
    private function validateScores(Validator $validator): void
    {
        if ($validator->errors()->isNotEmpty()) {
            return;
        }

        $game = $this->game();

        $scoreTypes = $this->scoreTypes();
        $submitted = $this->array('scores');

        $attendeeIds = $game->attendees()->pluck('event_attendees.id')
            ->map(fn (int $id): int => $id)
            ->sort()
            ->values();

        $submittedIds = collect(array_keys($submitted))
            ->map(fn (int|string $id): int => (int) $id)
            ->sort()
            ->values();

        if ($submittedIds->all() !== $attendeeIds->all()) {
            $validator->errors()->add('scores', 'Scores must be submitted for every attendee in this game.');

            return;
        }

        foreach ($submitted as $attendeeId => $scores) {
            foreach (array_keys($scores) as $slug) {
                $scoreType = $scoreTypes->get($slug);

                if (! $scoreType instanceof EventScoreType) {
                    $validator->errors()->add("scores.{$attendeeId}.{$slug}", 'This event has no such score type.');

                    continue;
                }

                if ($scoreType->is_derived) {
                    $validator->errors()->add("scores.{$attendeeId}.{$slug}", "{$scoreType->name} is calculated from the submitted scores and cannot be supplied.");
                }
            }
        }
    }
}
