<?php

namespace App\Models;

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
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Game newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Game newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Game query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Game whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Game whereEditedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Game whereEditedByUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Game whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Game whereIsBye($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Game whereRoundId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Game whereSubmittedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Game whereSubmittedByUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Game whereTableNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Game whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Game extends Model
{
    /** @use HasFactory<GameFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'round_id',
        'table_number',
        'is_bye',
        'submitted_by_user_id',
        'submitted_at',
        'edited_by_user_id',
        'edited_at',
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
     * @return BelongsToMany<EventAttendee, $this, GameAttendeePivot>
     */
    public function attendees(): BelongsToMany
    {
        return $this->belongsToMany(EventAttendee::class, 'game_attendee')
            ->using(GameAttendeePivot::class)
            ->withTimestamps();
    }
}
