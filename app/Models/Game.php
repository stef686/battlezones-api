<?php

namespace App\Models;

use App\Enums\SortDirection;
use Database\Factories\GameFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $round_id
 * @property int|null $table_number
 * @property bool $is_bye
 * @property int|null $submitted_by_user_id
 * @property Carbon|null $submitted_at
 * @property int|null $edited_by_user_id
 * @property Carbon|null $edited_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read GameAttendeePivot|null $pivot
 * @property-read Collection<int, EventAttendee> $attendees
 * @property-read int|null $attendees_count
 * @property-read User|null $editedBy
 * @property-read GameResultFlag|null $openResultFlag
 * @property-read Collection<int, GameResultFlag> $resultFlags
 * @property-read int|null $result_flags_count
 * @property-read Round $round
 * @property-read Collection<int, GameScore> $scores
 * @property-read int|null $scores_count
 * @property-read User|null $submittedBy
 *
 * @method static \Database\Factories\GameFactory factory($count = null, $state = [])
 * @method static Builder<static>|Game newModelQuery()
 * @method static Builder<static>|Game newQuery()
 * @method static Builder<static>|Game query()
 * @method static Builder<static>|Game whereCreatedAt($value)
 * @method static Builder<static>|Game whereEditedAt($value)
 * @method static Builder<static>|Game whereEditedByUserId($value)
 * @method static Builder<static>|Game whereId($value)
 * @method static Builder<static>|Game whereIsBye($value)
 * @method static Builder<static>|Game whereRoundId($value)
 * @method static Builder<static>|Game whereSubmittedAt($value)
 * @method static Builder<static>|Game whereSubmittedByUserId($value)
 * @method static Builder<static>|Game whereTableNumber($value)
 * @method static Builder<static>|Game whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Game extends Model
{
    /** @use HasFactory<GameFactory> */
    use HasFactory;

    /**
     * Result attribution is deliberately absent: `submitted_by_user_id`,
     * `submitted_at`, `edited_by_user_id` and `edited_at` say who claimed a
     * result and when, so they are written by the controllers that establish
     * that fact and never from a request payload.
     *
     * @var list<string>
     */
    protected $fillable = [
        'round_id',
        'table_number',
        'is_bye',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_bye' => 'boolean',
            'submitted_at' => 'datetime',
            'edited_at' => 'datetime',
        ];
    }

    /**
     * Whether a result has been submitted, which locks the Game to Players.
     */
    public function hasResult(): bool
    {
        return $this->submitted_at !== null;
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by_user_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function editedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'edited_by_user_id');
    }

    /**
     * @return BelongsTo<Round, $this>
     */
    public function round(): BelongsTo
    {
        return $this->belongsTo(Round::class);
    }

    /**
     * The Players sitting at this Game — all four in doubles.
     *
     * @return Builder<User>
     */
    public function players(): Builder
    {
        return User::query()->whereIn(
            'id',
            EventAttendeeMembership::query()
                ->whereIn('event_attendee_id', $this->attendees()->select('event_attendees.id'))
                ->select('user_id'),
        );
    }

    /**
     * @return HasMany<GameResultFlag, $this>
     */
    public function resultFlags(): HasMany
    {
        return $this->hasMany(GameResultFlag::class);
    }

    /**
     * The open flag on this Game, if a Player or Organiser has raised one.
     *
     * @return HasOne<GameResultFlag, $this>
     */
    public function openResultFlag(): HasOne
    {
        return $this->hasOne(GameResultFlag::class)->unresolved();
    }

    /**
     * @return HasMany<GameScore, $this>
     */
    public function scores(): HasMany
    {
        return $this->hasMany(GameScore::class);
    }

    /**
     * The Attendee who won this Game, or null where nobody did.
     *
     * Decided the same way the Standings are: the ranked Score Types in
     * `ranking_order`, each read in its own `sort_direction`, first one that
     * separates the two wins it. Anything else is a draw — including a Game
     * nobody has played, where every score is absent and therefore equal.
     *
     * A Bye is a win by the shape of the draw rather than by a scoreline, so
     * its lone Attendee is the winner from the moment it is paired. This is
     * the same fact `StoreGameScores::awardByeWin()` writes Match Points for.
     *
     * Reads the `attendees` and `scores` in hand rather than querying per
     * Game — a Round is decided one Game at a time, and a lookup inside that
     * loop is the N+1 the eager loads exist to avoid. Loaded here only where
     * a caller has not, which costs nothing when they have.
     *
     * @param  Collection<int, EventScoreType>  $scoreTypes
     */
    public function winningAttendeeId(Collection $scoreTypes): ?int
    {
        $this->loadMissing(['attendees', 'scores']);

        if ($this->is_bye) {
            return $this->attendees->first()?->id;
        }

        if ($this->attendees->count() !== 2) {
            return null;
        }

        $ranked = $scoreTypes->whereNotNull('ranking_order')->sortBy('ranking_order');

        [$left, $right] = [$this->attendees[0]->id, $this->attendees[1]->id];

        foreach ($ranked as $scoreType) {
            $comparison = bccomp(
                $this->scoreFor($left, $scoreType),
                $this->scoreFor($right, $scoreType),
                2,
            );

            if ($comparison === 0) {
                continue;
            }

            $leftIsBetter = $scoreType->sort_direction === SortDirection::Desc
                ? $comparison > 0
                : $comparison < 0;

            return $leftIsBetter ? $left : $right;
        }

        return null;
    }

    /** One Attendee's score under one Score Type, absent reading as zero. */
    private function scoreFor(int $attendeeId, EventScoreType $scoreType): string
    {
        $score = $this->scores
            ->first(fn (GameScore $row): bool => $row->event_attendee_id === $attendeeId
                && $row->event_score_type_id === $scoreType->id);

        return $score === null ? '0' : (string) $score->value;
    }

    /**
     * The Attendees at this table, in the order they were paired.
     *
     * Ordered rather than left to the database: pairing keeps each Game's
     * first Attendee and exchanges the second (see SwapRoundPairings), so
     * "first" and "second" have to mean the same thing to the action that
     * swaps them and to the client previewing the swap.
     *
     * @return BelongsToMany<EventAttendee, $this, GameAttendeePivot>
     */
    public function attendees(): BelongsToMany
    {
        return $this->belongsToMany(EventAttendee::class, 'game_attendee')
            ->using(GameAttendeePivot::class)
            ->orderBy('game_attendee.id')
            ->withTimestamps();
    }
}
