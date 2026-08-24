<?php

namespace Database\Seeders;

use App\Actions\Events\StoreGameScores;
use App\Casts\EventSettings;
use App\Enums\Allegiance;
use App\Enums\EventInviteRole;
use App\Enums\EventOrganiserRole;
use App\Enums\EventStatus;
use App\Enums\FeedbackQuestionType;
use App\Enums\PairingFormat;
use App\Enums\PollType;
use App\Enums\RoundStatus;
use App\Enums\ScheduleBlockType;
use App\Models\Event;
use App\Models\EventAttendee;
use App\Models\EventAttendeeMembership;
use App\Models\EventInvite;
use App\Models\EventPoll;
use App\Models\EventScheduleBlock;
use App\Models\EventScoreType;
use App\Models\Faction;
use App\Models\FeedbackInvitation;
use App\Models\FeedbackQuestion;
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

    /** Runs the Event, so the browser test can publish a Round through the API. */
    public const ORGANISER_EMAIL = 'organiser@battlezones.test';

    /** The table the unpublished second Round puts the Player on. */
    public const NEXT_TABLE_NUMBER = 12;

    /** The second table of the unpublished Round, so a swap has two Games. */
    public const NEXT_OTHER_TABLE_NUMBER = 13;

    /**
     * A fixed token so the browser test can follow the link an Organiser's
     * invitation email would carry. Only ever stored hashed, as in production.
     */
    public const INVITE_TOKEN = 'end-to-end-invite-token';

    /**
     * A fixed feedback link, so the browser test can open the one the Player
     * would have been emailed. Stored hashed, as in production.
     */
    public const FEEDBACK_TOKEN = 'end-to-end-feedback-token';

    public function run(): void
    {
        $event = $this->event();

        $this->scoreTypes($event);

        $mine = $this->attendee($event, self::PLAYER_EMAIL, 'Ada Lovelace', Allegiance::Loyalist);
        $theirs = $this->attendee($event, self::OPPONENT_EMAIL, 'Grace Hopper', Allegiance::Traitor);

        // A second pair, so the Draft Round has two Games to swap between, and
        // a fifth party who takes the Bye an odd field produces.
        $otherLoyalist = $this->attendee($event, 'sanguinius@battlezones.test', 'Sanguinius', Allegiance::Loyalist);
        $otherTraitor = $this->attendee($event, 'curze@battlezones.test', 'Konrad Curze', Allegiance::Traitor);
        $unpaired = $this->attendee($event, 'ferrus@battlezones.test', 'Ferrus Manus', Allegiance::Loyalist);

        $round = $this->game($event, $mine, $theirs);

        $this->bye($round, $unpaired);

        $this->schedule($event, $round);

        $this->nextRound($event, $mine, $theirs, $otherLoyalist, $otherTraitor);

        $this->paintingPoll($event, [$otherLoyalist, $otherTraitor]);

        $this->organiser($event);

        $this->invite($event);

        $this->unregisteredCaptain($event);

        $this->feedback($event, $mine);
    }

    /**
     * The questions the form asks, and an unspent link into it.
     *
     * Reset on every run: the browser test submits the form, which spends the
     * link, and the next run needs it usable again.
     */
    private function feedback(Event $event, EventAttendee $attendee): void
    {
        $questions = [
            ['key' => 'overall', 'prompt' => 'Overall, how was the event?', 'type' => FeedbackQuestionType::Rating, 'display_order' => 0],
            ['key' => 'venue', 'prompt' => 'How were the venue and the tables?', 'type' => FeedbackQuestionType::Rating, 'display_order' => 1],
            ['key' => 'anything_else', 'prompt' => 'Anything else the organisers should know?', 'type' => FeedbackQuestionType::Text, 'display_order' => 2],
        ];

        foreach ($questions as $question) {
            FeedbackQuestion::query()->updateOrCreate(['key' => $question['key']], $question);
        }

        $player = $attendee->memberships()->firstOrFail()->user;

        FeedbackInvitation::query()->updateOrCreate(
            ['token' => FeedbackInvitation::hashToken(self::FEEDBACK_TOKEN)],
            [
                'event_id' => $event->getKey(),
                'user_id' => $player->getKey(),
                'sent_at' => now(),
                'expires_at' => now()->addDays(FeedbackInvitation::LIFETIME_DAYS),
                'submitted_at' => null,
            ],
        );
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
                    // Three, so the Organiser loop has somewhere to go: pairing
                    // is refused once the scheduled count is reached.
                    roundCount: 3,
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

        // Reset on every run: the browser test submits a list, which locks it,
        // and a locked list would refuse the next run's submission.
        $attendee->memberships()->where('user_id', $user->getKey())->update([
            'army_list' => 'Legion Tactical Squad, 10 models.',
            'army_list_submitted_at' => null,
        ]);

        $attendee->forceFill([
            'army_lists_revealed_at' => null,
            // The browser test enters this team in the painting Poll, so the
            // next run has to find it out of the vote again.
            'painting_entered' => false,
        ])->save();

        return $attendee;
    }

    /**
     * A second Round, held back in Draft.
     *
     * The browser test publishes it through the API and watches a Player's
     * screen follow, so it has to start every run unpublished and pointing at
     * a different table from the first.
     */
    private function nextRound(
        Event $event,
        EventAttendee $mine,
        EventAttendee $theirs,
        EventAttendee $otherLoyalist,
        EventAttendee $otherTraitor,
    ): void {
        // A browser test that pairs the next Round leaves a third one behind,
        // and the Rounds list is asserted on by count. The fixture owns two.
        $later = Round::query()->where('event_id', $event->getKey())->where('number', '>', 2)->get();

        foreach ($later as $extra) {
            $extra->games()->each(function (Game $game): void {
                $game->scores()->delete();
                $game->resultFlags()->delete();
                $game->attendees()->detach();
                $game->delete();
            });

            $extra->delete();
        }

        $round = Round::query()->updateOrCreate(
            ['event_id' => $event->getKey(), 'number' => 2],
            ['name' => 'Round 2', 'status' => RoundStatus::Draft],
        );

        $pairs = [
            self::NEXT_TABLE_NUMBER => [$mine, $theirs],
            self::NEXT_OTHER_TABLE_NUMBER => [$otherLoyalist, $otherTraitor],
        ];

        foreach ($pairs as $table => [$loyalist, $traitor]) {
            $game = Game::query()->updateOrCreate(
                ['round_id' => $round->getKey(), 'table_number' => $table],
                [
                    'is_bye' => false,
                    'submitted_at' => null,
                    'submitted_by_user_id' => null,
                    'edited_at' => null,
                    'edited_by_user_id' => null,
                ],
            );

            $game->scores()->delete();
            $game->resultFlags()->delete();
            // Detached first so pairing order is rewritten every run: a swap
            // exchanges the second Attendee, and the browser test asserts on
            // which one that is.
            $game->attendees()->detach();
            $game->attendees()->attach($loyalist->getKey());
            $game->attendees()->attach($traitor->getKey());
        }
    }

    /**
     * The Bye an odd field produces, in the Round being played.
     *
     * The win is awarded here exactly as `GenerateRoundPairings` awards it, so
     * the fixture matches what real pairing produces: match points from the
     * moment the Round is paired, victory points waiting on an Organiser.
     */
    private function bye(Round $round, EventAttendee $unpaired): void
    {
        $bye = Game::query()->updateOrCreate(
            ['round_id' => $round->getKey(), 'table_number' => null],
            [
                'is_bye' => true,
                'submitted_at' => null,
                'submitted_by_user_id' => null,
                'edited_at' => null,
                'edited_by_user_id' => null,
            ],
        );

        $bye->scores()->delete();
        $bye->attendees()->sync([$unpaired->getKey()]);

        app(StoreGameScores::class)->awardByeWin($bye->fresh(['round']));
    }

    /**
     * A painting Poll nobody has opened yet, with two armies on the table.
     *
     * Reset on every run: the browser test opens it, votes in it and closes
     * it, and the next run needs the window shut and the votes gone.
     *
     * @param  list<EventAttendee>  $entered
     */
    private function paintingPoll(Event $event, array $entered): void
    {
        $poll = EventPoll::query()->updateOrCreate(
            ['event_id' => $event->getKey(), 'name' => 'Best Painted Army'],
            [
                'type' => PollType::Painting,
                'votes_per_player' => 2,
                'opens_at' => null,
                'closes_at' => null,
            ],
        );

        $poll->votes()->delete();

        foreach ($entered as $attendee) {
            $attendee->forceFill(['painting_entered' => true])->save();
        }
    }

    private function organiser(Event $event): void
    {
        $organiser = User::query()->updateOrCreate(
            ['email' => self::ORGANISER_EMAIL],
            [
                'name' => 'Rogal Dorn',
                'password' => Hash::make(self::PASSWORD),
                'email_verified_at' => now(),
                'claimed_at' => now(),
            ],
        );

        $event->organisers()->syncWithoutDetaching([
            $organiser->getKey() => ['role' => EventOrganiserRole::Lead->value],
        ]);
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
        // Flags raised by an earlier run would still be waiting in the
        // Organiser's queue, which the queue test reads as its own.
        $game->resultFlags()->delete();
        $game->attendees()->sync([$mine->getKey(), $theirs->getKey()]);

        return $round;
    }
}
