<?php

namespace Database\Seeders;

use App\Casts\EventSettings;
use App\Enums\EventStatus;
use App\Enums\PairingFormat;
use App\Enums\PollType;
use App\Enums\RegistrationMode;
use App\Enums\SortDirection;
use App\Models\Event;
use App\Models\EventPoll;
use App\Models\EventScoreType;
use App\Models\GameSystem;
use Illuminate\Database\Seeder;

/**
 * The Horus Heresy Doubles event, in version control rather than typed into a
 * console once and never repeatable.
 *
 * Nothing here is fabricated: no Attendees, no Games, no Standings. The
 * seeder creates the Event and the configuration it cannot run without, and
 * the field arrives by registration.
 *
 * Multi-event support is already real — Events are addressed by slug on one
 * API host — so `horusheresydoubles.battlezones.net` is a front end calling
 * this same API, and DNS stays the one manual step.
 */
class HorusHeresyDoublesSeeder extends Seeder
{
    public const SLUG = 'horus-heresy-doubles';

    /**
     * Dates and venue are the Organiser's to confirm; everything else here is
     * the configuration the Event genuinely cannot be paired or scored without.
     */
    private const STARTS_AT = '2026-10-10 09:00:00';

    private const ENDS_AT = '2026-10-11 17:00:00';

    public function run(): void
    {
        $system = GameSystem::query()->firstOrCreate(
            ['slug' => 'horus-heresy'],
            ['name' => 'The Horus Heresy'],
        );

        $event = Event::query()->updateOrCreate(
            ['slug' => self::SLUG],
            [
                'game_system_id' => $system->getKey(),
                'name' => 'Horus Heresy Doubles',
                'description' => 'A two-day doubles event: Loyalists against Traitors, five rounds, one weekend.',
                'status' => EventStatus::Published,
                'pairing_format' => PairingFormat::Swiss,
                'starts_at' => self::STARTS_AT,
                'ends_at' => self::ENDS_AT,
                'venue_name' => 'To be confirmed',
                'venue_city' => 'To be confirmed',
                'timezone' => 'Europe/London',
                'max_attendees' => 32,
                'attendee_size' => 2,
                'registration_mode' => RegistrationMode::Open,
                'settings' => new EventSettings(
                    requiresOpposedAllegiance: true,
                    roundCount: 5,
                    standingsVisible: true,
                ),
            ],
        );

        $this->seedScoreTypes($event);
        $this->seedPolls($event);
    }

    /**
     * Match Points rank the field and Victory Points break the ties, which is
     * the precedence `ranking_order` carries.
     */
    private function seedScoreTypes(Event $event): void
    {
        EventScoreType::query()->updateOrCreate(
            ['event_id' => $event->getKey(), 'slug' => 'match-points'],
            [
                'name' => 'Match Points',
                'sort_direction' => SortDirection::Desc,
                'is_derived' => true,
                'ranking_order' => 1,
                'win_points' => 3,
                'draw_points' => 1,
                'loss_points' => 0,
                'display_order' => 0,
            ],
        );

        EventScoreType::query()->updateOrCreate(
            ['event_id' => $event->getKey(), 'slug' => 'victory-points'],
            [
                'name' => 'Victory Points',
                'sort_direction' => SortDirection::Desc,
                'is_derived' => false,
                'ranking_order' => 2,
                'win_points' => null,
                'draw_points' => null,
                'loss_points' => null,
                'display_order' => 1,
            ],
        );
    }

    /**
     * Both Polls arrive closed: painting voting is opened from the display
     * table on the day, and favourite-opponent voting opens itself per team as
     * each finishes its last Game.
     */
    private function seedPolls(Event $event): void
    {
        EventPoll::query()->updateOrCreate(
            ['event_id' => $event->getKey(), 'type' => PollType::Painting],
            [
                'name' => 'Best Painted Army',
                'votes_per_player' => 3,
                'opens_at' => null,
                'closes_at' => null,
            ],
        );

        EventPoll::query()->updateOrCreate(
            ['event_id' => $event->getKey(), 'type' => PollType::FavouriteOpponent],
            [
                'name' => 'Favourite Opponent',
                'votes_per_player' => 1,
                'opens_at' => null,
                'closes_at' => null,
            ],
        );
    }
}
