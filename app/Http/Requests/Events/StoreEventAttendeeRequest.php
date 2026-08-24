<?php

namespace App\Http\Requests\Events;

use App\Enums\Allegiance;
use App\Models\Event;
use App\Models\EventAttendeeMembership;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Knuckles\Scribe\Attributes\BodyParam;

#[BodyParam('name', 'string', 'The name the party competes under.', required: false, example: 'Sons of Terra')]
#[BodyParam('allegiance', 'string', 'The side the party fights for, where the Event divides the field.', required: false, example: 'loyalist')]
#[BodyParam('players', 'object[]', 'One entry per Player, including whoever is registering.', required: true)]
#[BodyParam('players[].name', 'string', 'The Player\'s name.', required: false, example: 'Tarik Torgaddon')]
#[BodyParam('players[].email', 'string', 'The Player\'s email address.', required: true, example: 'tarik@example.com')]
#[BodyParam('players[].faction_id', 'integer', 'The Faction this Player brings.', required: false, example: 1)]
#[BodyParam('players[].army_list', 'string', 'This Player\'s army list.', required: false)]
class StoreEventAttendeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $event = $this->event();

        return [
            'name' => ['nullable', 'string', 'max:255'],
            'allegiance' => [
                $event->settings->requiresOpposedAllegiance ? 'required' : 'nullable',
                Rule::enum(Allegiance::class),
            ],
            'players' => ['required', 'array', 'size:'.$event->attendee_size],
            'players.*.name' => ['nullable', 'string', 'max:255'],
            'players.*.email' => ['required', 'email', 'distinct:ignore_case'],
            'players.*.faction_id' => [
                'nullable',
                Rule::exists('factions', 'id')->where('game_system_id', $event->game_system_id),
            ],
            'players.*.army_list' => ['nullable', 'string'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $this->rejectPlayersAlreadyEntered($validator);
                $this->requireTheRegistrantAmongPlayers($validator);
            },
        ];
    }

    /**
     * A Player enters an Event once. The pivot guarantees it, but a duplicate
     * reaching the database would surface as a 500 rather than a form error.
     */
    private function rejectPlayersAlreadyEntered(Validator $validator): void
    {
        $entered = User::query()
            ->whereIn('email', $this->emails())
            ->whereIn('id', EventAttendeeMembership::query()
                ->where('event_id', $this->event()->getKey())
                ->select('user_id'))
            ->pluck('email', 'id');

        foreach ($this->emails() as $index => $email) {
            if ($entered->contains(fn (string $taken): bool => mb_strtolower($taken) === mb_strtolower($email))) {
                $validator->errors()->add(
                    "players.{$index}.email",
                    'This player has already entered this event.',
                );
            }
        }
    }

    /**
     * Someone registering a party is entering it, not filing it on another
     * team's behalf; an Organiser doing that for someone else is the one case
     * where they may leave themselves out.
     */
    private function requireTheRegistrantAmongPlayers(Validator $validator): void
    {
        $registrant = $this->user();

        if (! $registrant instanceof User || $this->event()->isOrganisedBy($registrant)) {
            return;
        }

        $emails = array_map(mb_strtolower(...), $this->emails());

        if (! in_array(mb_strtolower($registrant->email), $emails, true)) {
            $validator->errors()->add('players', 'Your own details must be among the players.');
        }
    }

    /**
     * @return array<int, string>
     */
    private function emails(): array
    {
        /** @var array<int, array{email?: mixed}> $players */
        $players = $this->input('players', []);

        return array_map(fn (array $player): string => (string) ($player['email'] ?? ''), $players);
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
}
