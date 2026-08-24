<?php

namespace App\Http\Requests\Events;

use App\Models\Event;
use App\Models\EventScoreType;
use App\Models\Game;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;

/**
 * Shared shape of a Game result write, whether a Player submits it or an
 * Organiser corrects it: scores keyed by Attendee, never derived Score Types.
 */
abstract class GameResultRequest extends FormRequest
{
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

    /**
     * Empty when route model binding has not run, which only happens where the
     * docs generator instantiates this request outside a real request cycle.
     */
    public function event(): Event
    {
        $event = $this->route('event');

        return $event instanceof Event ? $event : new Event();
    }

    /**
     * Empty when route model binding has not run, which only happens where the
     * docs generator instantiates this request outside a real request cycle.
     */
    public function game(): Game
    {
        $game = $this->route('game');

        return $game instanceof Game ? $game : new Game();
    }

    /**
     * The Event's Score Types, keyed by slug.
     *
     * @return Collection<string, EventScoreType>
     */
    public function scoreTypes(): Collection
    {
        return $this->event()->scoreTypes()->get()->keyBy('slug');
    }

    /**
     * The submitted scores translated to the shape `StoreGameScores` takes.
     *
     * @return array<int, array<int, numeric-string|int|float>>
     */
    public function scoresByAttendeeId(): array
    {
        $scoreTypeIds = $this->scoreTypes()->map(fn (EventScoreType $scoreType): int => $scoreType->getKey());

        /** @var array<int|string, array<string, numeric-string|int|float>> $submitted */
        $submitted = $this->array('scores');

        return collect($submitted)
            ->mapWithKeys(fn (array $scores, int|string $attendeeId): array => [
                (int) $attendeeId => collect($scores)
                    ->mapWithKeys(fn (int|float|string $value, string $slug): array => [$scoreTypeIds->get($slug) => $value])
                    ->all(),
            ])
            ->all();
    }

    /**
     * A result is scored for the whole Game, so a partial write is a
     * half-recorded Game rather than a smaller version of a valid one.
     */
    private function validateScores(Validator $validator): void
    {
        if ($validator->errors()->isNotEmpty()) {
            return;
        }

        $scoreTypes = $this->scoreTypes();
        $submitted = $this->array('scores');

        $attendeeIds = $this->game()->attendees()->pluck('event_attendees.id')
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
