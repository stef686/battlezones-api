<?php

namespace Database\Seeders;

use App\Enums\CustomFieldType;
use App\Enums\SortDirection;
use App\Models\Club;
use App\Models\Event;
use App\Models\EventAttendee;
use App\Models\EventCustomField;
use App\Models\EventCustomFieldResponse;
use App\Models\EventDocument;
use App\Models\EventScoreType;
use App\Models\EventStanding;
use App\Models\EventStandingScore;
use App\Models\EventUpdate;
use App\Models\EventUpdateAttachment;
use App\Models\Faction;
use App\Models\Game;
use App\Models\GameScore;
use App\Models\GameSystem;
use App\Models\Photo;
use App\Models\Reaction;
use App\Models\Round;
use App\Models\User;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        $gameSystems = $this->createGameSystems();
        $clubs = $this->createClubs();
        $users = $this->createUsers($clubs);

        $this->createDraftEvent($gameSystems[0], $clubs[0], $users);
        $this->createPublishedEvent($gameSystems[1], $clubs[1], $users);
        $this->createActiveEvent($gameSystems[0], $clubs[0], $users);
        $this->createCompletedEvent($gameSystems[1], $clubs[2], $users);
        $this->createCancelledEvent($gameSystems[0], null, $users);
    }

    /**
     * @return list<GameSystem>
     */
    private function createGameSystems(): array
    {
        return [
            $this->createGameSystemWithFactions('Warhammer 40,000', [
                'Space Marines', 'Orks', 'Aeldari', 'Tyranids', 'Necrons', 'Chaos Space Marines',
            ]),
            $this->createGameSystemWithFactions('Age of Sigmar', [
                'Stormcast Eternals', 'Skaven', 'Lumineth Realm-lords', 'Orruk Warclans',
            ]),
        ];
    }

    private function createGameSystemWithFactions(string $name, array $factionNames): GameSystem
    {
        $system = GameSystem::factory()->create(['name' => $name, 'slug' => str($name)->slug()]);

        foreach ($factionNames as $factionName) {
            Faction::factory()->for($system)->create([
                'name' => $factionName,
                'slug' => str($factionName)->slug(),
            ]);
        }

        return $system;
    }

    /**
     * @return list<Club>
     */
    private function createClubs(): array
    {
        return [
            Club::factory()->create(['name' => 'London Warlords', 'slug' => 'london-warlords', 'city' => 'London']),
            Club::factory()->create(['name' => 'Manchester Maulers', 'slug' => 'manchester-maulers', 'city' => 'Manchester']),
            Club::factory()->create(['name' => 'Bristol Berserkers', 'slug' => 'bristol-berserkers', 'city' => 'Bristol']),
        ];
    }

    /**
     * @return list<User>
     */
    private function createUsers(array $clubs): array
    {
        $users = User::factory()->count(20)->create();

        foreach ($clubs as $club) {
            $club->members()->attach($users->random(rand(4, 8))->pluck('id'));
        }

        return $users->all();
    }

    private function createDraftEvent(GameSystem $system, Club $club, array $users): void
    {
        Event::factory()->draft()->for($system)->for($club)->create([
            'name' => 'Summer Showdown 2026',
            'slug' => 'summer-showdown-2026',
            'venue_name' => 'Warhammer World',
            'venue_city' => 'Nottingham',
            'starts_at' => now()->addMonths(3),
            'ends_at' => now()->addMonths(3)->addDay(),
        ]);
    }

    private function createPublishedEvent(GameSystem $system, Club $club, array $users): void
    {
        $event = Event::factory()->published()->for($system)->for($club)->create([
            'name' => 'Northern Crusade',
            'slug' => 'northern-crusade',
            'venue_name' => 'Manchester Convention Centre',
            'venue_city' => 'Manchester',
            'starts_at' => now()->addMonth(),
            'ends_at' => now()->addMonth()->addDay(),
        ]);

        $this->addDocuments($event);
        $this->addCustomFields($event);
    }

    private function createActiveEvent(GameSystem $system, Club $club, array $users): void
    {
        $event = Event::factory()->active()->standingsVisible()->for($system)->for($club)->create([
            'name' => 'London Grand Tournament',
            'slug' => 'london-grand-tournament',
            'venue_name' => 'ExCeL London',
            'venue_city' => 'London',
            'max_attendees' => 32,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
        ]);

        $factions = $system->factions;
        $attendees = collect($users)->take(16)->map(fn (User $user) => EventAttendee::factory()
            ->for($event)
            ->withMember($user, ['faction_id' => $factions->random()->id])
            ->create(['checked_in_at' => now()->subDay()])
        );

        $scoreTypes = $this->addScoreTypes($event);
        $this->addCustomFields($event);
        $this->addCustomFieldResponses($event, $attendees);
        $this->addDocuments($event);
        $this->addUpdates($event, $users);
        $this->addPhotos($event, $users);

        $round1 = Round::factory()->for($event)->create(['number' => 1, 'name' => 'Round 1']);
        $this->createGamesForRound($round1, $attendees, $scoreTypes);

        $round2 = Round::factory()->for($event)->create(['number' => 2, 'name' => 'Round 2']);
        $this->createGamesForRound($round2, $attendees, $scoreTypes);

        Round::factory()->for($event)->create(['number' => 3, 'name' => 'Round 3']);

        $this->createPartialStandings($event, $attendees, $scoreTypes);
    }

    private function createCompletedEvent(GameSystem $system, Club $club, array $users): void
    {
        $event = Event::factory()->completed()->standingsVisible()->for($system)->for($club)->create([
            'name' => 'Bristol Bash 2025',
            'slug' => 'bristol-bash-2025',
            'venue_name' => 'Colston Hall',
            'venue_city' => 'Bristol',
            'max_attendees' => 24,
            'starts_at' => now()->subWeeks(2),
            'ends_at' => now()->subWeeks(2)->addDay(),
        ]);

        $factions = $system->factions;
        $attendees = collect($users)->take(12)->map(fn (User $user) => EventAttendee::factory()
            ->for($event)
            ->withMember($user, ['faction_id' => $factions->random()->id])
            ->create(['checked_in_at' => now()->subWeeks(2)])
        );

        $scoreTypes = $this->addScoreTypes($event);
        $this->addDocuments($event);
        $this->addUpdates($event, $users);
        $this->addPhotos($event, $users);

        foreach (range(1, 3) as $number) {
            $round = Round::factory()->for($event)->create(['number' => $number, 'name' => "Round {$number}"]);
            $this->createGamesForRound($round, $attendees, $scoreTypes);
        }

        $this->createFullStandings($event, $attendees, $scoreTypes);
    }

    private function createCancelledEvent(GameSystem $system, ?Club $club, array $users): void
    {
        Event::factory()->cancelled()->for($system)->create([
            'name' => 'Cancelled Cup',
            'slug' => 'cancelled-cup',
            'venue_name' => 'Village Hall',
            'venue_city' => 'Oxford',
            'starts_at' => now()->addWeek(),
            'ends_at' => now()->addWeek()->addDay(),
        ]);
    }

    /**
     * @return list<EventScoreType>
     */
    private function addScoreTypes(Event $event): array
    {
        return [
            EventScoreType::factory()->for($event)->create([
                'name' => 'Battle Points',
                'slug' => 'battle-points',
                'sort_direction' => SortDirection::Desc,
                'display_order' => 0,
            ]),
            EventScoreType::factory()->for($event)->create([
                'name' => 'Sportsmanship',
                'slug' => 'sportsmanship',
                'sort_direction' => SortDirection::Desc,
                'display_order' => 1,
            ]),
        ];
    }

    private function addDocuments(Event $event): void
    {
        EventDocument::factory()->for($event)->create(['name' => 'Players Pack']);
        EventDocument::factory()->for($event)->create(['name' => 'Rules FAQ']);
    }

    private function addCustomFields(Event $event): void
    {
        EventCustomField::factory()->for($event)->create([
            'name' => 'Team Name',
            'type' => CustomFieldType::Text,
            'display_order' => 0,
        ]);
        EventCustomField::factory()->for($event)->create([
            'name' => 'Experience Level',
            'type' => CustomFieldType::Select,
            'options' => ['Beginner', 'Intermediate', 'Advanced', 'Veteran'],
            'display_order' => 1,
        ]);
        EventCustomField::factory()->for($event)->create([
            'name' => 'Bringing Display Board',
            'type' => CustomFieldType::Checkbox,
            'display_order' => 2,
        ]);
    }

    private function addCustomFieldResponses(Event $event, $attendees): void
    {
        $fields = EventCustomField::query()->where('event_id', $event->id)->get();

        foreach ($attendees as $attendee) {
            foreach ($fields as $field) {
                $value = match ($field->type) {
                    CustomFieldType::Text => fake()->words(2, true),
                    CustomFieldType::Select => fake()->randomElement($field->options ?? ['N/A']),
                    CustomFieldType::Checkbox => fake()->boolean() ? '1' : '0',
                    default => fake()->word(),
                };

                EventCustomFieldResponse::factory()
                    ->for($attendee, 'attendee')
                    ->for($field, 'field')
                    ->create(['value' => $value]);
            }
        }
    }

    private function addUpdates(Event $event, array $users): void
    {
        EventUpdate::factory()->pinned()->for($event)->for($users[0], 'author')->create([
            'title' => 'Welcome to '.$event->name,
            'body' => 'We are excited to welcome everyone to this event. Please check the documents section for the players pack.',
        ]);

        $update = EventUpdate::factory()->for($event)->for($users[1], 'author')->create([
            'title' => 'Round 1 Pairings Posted',
            'body' => 'Round 1 pairings are now live. Please check your table assignments.',
        ]);

        EventUpdateAttachment::factory()->for($update)->create(['name' => 'Round 1 Pairings.pdf']);

        EventUpdate::factory()->for($event)->for($users[0], 'author')->create([
            'title' => 'Lunch Break',
            'body' => 'Lunch will be served in the main hall from 12:00 to 13:00.',
        ]);
    }

    private function addPhotos(Event $event, array $users): void
    {
        $photos = collect($users)->take(6)->map(fn (User $user) => Photo::factory()->for($user)->create(['event_id' => $event->id])
        );

        foreach ($photos->take(4) as $photo) {
            $reactingUsers = collect($users)->random(rand(1, 5));
            foreach ($reactingUsers as $user) {
                Reaction::factory()->for($photo, 'reactable')->create(['user_id' => $user->id]);
            }
        }
    }

    private function createGamesForRound(Round $round, $attendees, array $scoreTypes): void
    {
        $shuffled = $attendees->shuffle();
        $pairs = $shuffled->chunk(2);

        $tableNumber = 1;
        foreach ($pairs as $pair) {
            $players = $pair->values();

            if ($players->count() === 1) {
                $game = Game::factory()->bye()->for($round)->create(['table_number' => $tableNumber]);
                $game->attendees()->attach($players[0]->id);

                foreach ($scoreTypes as $scoreType) {
                    GameScore::factory()->create([
                        'game_id' => $game->id,
                        'event_attendee_id' => $players[0]->id,
                        'event_score_type_id' => $scoreType->id,
                        'value' => 10,
                    ]);
                }
            } else {
                $game = Game::factory()->for($round)->create(['table_number' => $tableNumber]);

                $score1 = fake()->numberBetween(0, 20);
                $score2 = 20 - $score1;

                $game->attendees()->attach($players[0]->id);
                $game->attendees()->attach($players[1]->id);

                foreach ([[$players[0], $score1], [$players[1], $score2]] as [$player, $score]) {
                    foreach ($scoreTypes as $index => $scoreType) {
                        $value = $index === 0 ? $score : fake()->numberBetween(1, 5);
                        GameScore::factory()->create([
                            'game_id' => $game->id,
                            'event_attendee_id' => $player->id,
                            'event_score_type_id' => $scoreType->id,
                            'value' => $value,
                        ]);
                    }
                }
            }

            $tableNumber++;
        }
    }

    private function createPartialStandings(Event $event, $attendees, array $scoreTypes): void
    {
        $primaryScoreType = $scoreTypes[0];

        $sorted = $attendees->sortByDesc(fn ($a) => GameScore::query()
            ->where('event_attendee_id', $a->id)
            ->where('event_score_type_id', $primaryScoreType->id)
            ->sum('value')
        )->values();

        foreach ($sorted as $position => $attendee) {
            $standing = EventStanding::factory()->for($event)->create([
                'event_attendee_id' => $attendee->id,
                'position' => $position + 1,
            ]);

            foreach ($scoreTypes as $scoreType) {
                $total = GameScore::query()
                    ->where('event_attendee_id', $attendee->id)
                    ->where('event_score_type_id', $scoreType->id)
                    ->sum('value');

                EventStandingScore::factory()->for($standing, 'standing')->create([
                    'event_score_type_id' => $scoreType->id,
                    'value' => $total,
                ]);
            }
        }
    }

    private function createFullStandings(Event $event, $attendees, array $scoreTypes): void
    {
        $this->createPartialStandings($event, $attendees, $scoreTypes);
    }
}
