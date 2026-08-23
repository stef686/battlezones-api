<?php

namespace Database\Seeders;

use App\Casts\EventSettings;
use App\Enums\Allegiance;
use App\Enums\EventInviteRole;
use App\Enums\EventStatus;
use App\Enums\PairingFormat;
use App\Enums\RoundStatus;
use App\Models\Event;
use App\Models\EventAttendee;
use App\Models\EventInvite;
use App\Models\EventScoreType;
use App\Models\Faction;
use App\Models\Game;
use App\Models\GameSystem;
use App\Models\Round;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * The fixed world the end-to-end test drives.
 *
 * Everything the browser test asserts on is named here rather than generated,
 * so a failure means the flow broke rather than the fixture moved. It is
 * idempotent: re-running it resets the Game to unscored, which is what the
 * result-submission step needs on a second run.
 */
class EndToEndSeeder extends Seeder
{
    public const EVENT_SLUG = 'end-to-end-open';

    public const PLAYER_EMAIL = 'player@battlezones.test';

    public const OPPONENT_EMAIL = 'opponent@battlezones.test';

    public const PASSWORD = 'end-to-end-password';

    public const TABLE_NUMBER = 7;

    public const INVITED_EMAIL = 'invited@battlezones.test';

    /**
     * A fixed token so the browser test can follow the link an Organiser's
     * invitation email would carry. Only ever stored hashed, as in production.
     */
    public const INVITE_TOKEN = 'end-to-end-invite-token';

    public function run(): void
    {
        $event = $this->event();

        $this->scoreTypes($event);

        $mine = $this->attendee($event, self::PLAYER_EMAIL, 'Ada Lovelace', Allegiance::Loyalist);
        $theirs = $this->attendee($event, self::OPPONENT_EMAIL, 'Grace Hopper', Allegiance::Traitor);

        $this->game($event, $mine, $theirs);

        $this->invite($event);
    }

    /**
     * An outstanding Invite to an account nobody has claimed.
     *
     * Reset on every run: the browser test claims it, which sets a password
     * and revokes the Invite, and the next run needs the link to work again.
     */
    private function invite(Event $event): void
    {
        $user = User::query()->updateOrCreate(
            ['email' => self::INVITED_EMAIL],
            [
                'name' => 'Invited Captain',
                'password' => Hash::make(self::PASSWORD),
                'email_verified_at' => null,
                'claimed_at' => null,
            ],
        );

        EventInvite::query()->updateOrCreate(
            ['token' => EventInvite::hashToken(self::INVITE_TOKEN)],
            [
                'event_id' => $event->getKey(),
                'user_id' => $user->getKey(),
                'role' => EventInviteRole::Captain,
                'expires_at' => now()->addWeek(),
                'revoked_at' => null,
            ],
        );
    }

    private function event(): Event
    {
        $system = GameSystem::query()->firstOrCreate(
            ['slug' => 'horus-heresy'],
            ['name' => 'The Horus Heresy'],
        );

        return Event::query()->updateOrCreate(
            ['slug' => self::EVENT_SLUG],
            [
                'game_system_id' => $system->getKey(),
                'name' => 'End To End Open',
                'description' => 'The fixed Event the browser test plays through.',
                'status' => EventStatus::Active,
                'pairing_format' => PairingFormat::Swiss,
                'starts_at' => now()->startOfDay(),
                'ends_at' => now()->addDay()->endOfDay(),
                'venue_name' => 'The Test Hall',
                'venue_city' => 'London',
                'timezone' => 'Europe/London',
                // The browser test reads the Standings, so they are public here.
                'settings' => new EventSettings(
                    requiresOpposedAllegiance: true,
                    roundCount: 1,
                    standingsVisible: true,
                ),
            ],
        );
    }

    private function scoreTypes(Event $event): void
    {
        EventScoreType::query()->updateOrCreate(
            ['event_id' => $event->getKey(), 'slug' => 'match-points'],
            [
                'name' => 'Match Points',
                'sort_direction' => 'desc',
                'is_derived' => true,
                'ranking_order' => 1,
                'display_order' => 0,
                'win_points' => 3,
                'draw_points' => 1,
                'loss_points' => 0,
            ],
        );

        EventScoreType::query()->updateOrCreate(
            ['event_id' => $event->getKey(), 'slug' => 'victory-points'],
            [
                'name' => 'Victory Points',
                'sort_direction' => 'desc',
                'is_derived' => false,
                'ranking_order' => 2,
                'display_order' => 1,
            ],
        );
    }

    private function attendee(Event $event, string $email, string $name, Allegiance $allegiance): EventAttendee
    {
        $user = User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make(self::PASSWORD),
                'email_verified_at' => now(),
                'claimed_at' => now(),
            ],
        );

        $attendee = EventAttendee::query()->firstOrCreate(
            ['event_id' => $event->getKey(), 'name' => $name.' and partner'],
            ['allegiance' => $allegiance],
        );

        if (! $attendee->members()->whereKey($user->getKey())->exists()) {
            $legion = $allegiance === Allegiance::Loyalist ? 'Imperial Fists' : 'Sons of Horus';

            $faction = Faction::query()->firstOrCreate(
                ['slug' => str($legion)->slug()->toString()],
                ['game_system_id' => $event->game_system_id, 'name' => $legion],
            );

            $attendee->members()->attach($user, [
                'event_id' => $event->getKey(),
                'faction_id' => $faction->getKey(),
                'army_list' => 'Legion Tactical Squad, 10 models.',
            ]);
        }

        return $attendee;
    }

    private function game(Event $event, EventAttendee $mine, EventAttendee $theirs): void
    {
        $round = Round::query()->updateOrCreate(
            ['event_id' => $event->getKey(), 'number' => 1],
            ['name' => 'Round 1', 'status' => RoundStatus::Live],
        );

        $game = Game::query()->updateOrCreate(
            ['round_id' => $round->getKey(), 'table_number' => self::TABLE_NUMBER],
            [
                'is_bye' => false,
                // Reset on every run: the browser test submits a result, and a
                // Game already claimed would answer the second run with a 409.
                'submitted_at' => null,
                'submitted_by_user_id' => null,
                'edited_at' => null,
                'edited_by_user_id' => null,
            ],
        );

        $game->scores()->delete();
        $game->attendees()->sync([$mine->getKey(), $theirs->getKey()]);
    }
}
