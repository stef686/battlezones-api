<?php

namespace Database\Seeders;

use App\Casts\EventSettings;
use App\Enums\Allegiance;
use App\Enums\EventInviteRole;
use App\Enums\EventStatus;
use App\Enums\PairingFormat;
use App\Enums\RoundStatus;
use App\Enums\ScheduleBlockType;
use App\Models\Event;
use App\Models\EventAttendee;
use App\Models\EventAttendeeMembership;
use App\Models\EventInvite;
use App\Models\EventScheduleBlock;
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

    /** A claimed account that has not entered, so it can register in the browser. */
    public const CAPTAIN_EMAIL = 'captain@battlezones.test';

    /** The partner that Captain names, who is invited rather than pre-created. */
    public const PARTNER_EMAIL = 'partner@battlezones.test';

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

        $round = $this->game($event, $mine, $theirs);

        $this->schedule($event, $round);

        $this->invite($event);

        $this->unregisteredCaptain($event);
    }

    /**
     * A Captain outside the Event, ready to enter it.
     *
     * Reset on every run: the browser test registers this Captain and invites
     * a partner, and the next run needs both of them back outside.
     */
    private function unregisteredCaptain(Event $event): void
    {
        $captain = User::query()->updateOrCreate(
            ['email' => self::CAPTAIN_EMAIL],
            [
                'name' => 'Malcador the Sigillite',
                'password' => Hash::make(self::PASSWORD),
                'email_verified_at' => now(),
                'claimed_at' => now(),
            ],
        );

        $partner = User::query()->where('email', self::PARTNER_EMAIL)->first();

        $entered = EventAttendeeMembership::query()
            ->where('event_id', $event->getKey())
            ->whereIn('user_id', array_filter([$captain->getKey(), $partner?->getKey()]))
            ->pluck('event_attendee_id')
            ->unique();

        if ($entered->isNotEmpty()) {
            EventAttendeeMembership::query()->whereIn('event_attendee_id', $entered)->delete();
            EventAttendee::query()->whereIn('id', $entered)->delete();
        }

        if ($partner instanceof User) {
            EventInvite::query()->where('user_id', $partner->getKey())->delete();
            $partner->delete();
        }
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
                // Doubles, so the registration form asks for a partner.
                'attendee_size' => 2,
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

    /**
     * Two blocks on the first day, out of order on purpose: the browser test
     * asserts they are rendered by time rather than by the order they were
     * written.
     */
    private function schedule(Event $event, Round $round): void
    {
        $day = $event->starts_at->copy()->startOfDay();

        EventScheduleBlock::query()->updateOrCreate(
            ['event_id' => $event->getKey(), 'label' => 'Round 1'],
            [
                'type' => ScheduleBlockType::Round,
                'round_id' => $round->getKey(),
                'starts_at' => $day->copy()->setTime(9, 30),
                'ends_at' => $day->copy()->setTime(12, 0),
                'display_order' => 1,
            ],
        );

        EventScheduleBlock::query()->updateOrCreate(
            ['event_id' => $event->getKey(), 'label' => 'Registration'],
            [
                'type' => ScheduleBlockType::Info,
                'round_id' => null,
                'starts_at' => $day->copy()->setTime(8, 30),
                'ends_at' => $day->copy()->setTime(9, 15),
                'display_order' => 0,
            ],
        );
    }

    private function game(Event $event, EventAttendee $mine, EventAttendee $theirs): Round
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

        return $round;
    }
}
