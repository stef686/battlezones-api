<?php

namespace App\Models;

use App\Enums\PollType;
use Database\Factories\EventPollFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * A named vote within an Event.
 *
 * @property int $id
 * @property int $event_id
 * @property string $name
 * @property PollType $type
 * @property Carbon|null $opens_at
 * @property Carbon|null $closes_at
 * @property int $votes_per_player
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Event $event
 * @property-read Collection<int, EventVote> $votes
 * @property-read int|null $votes_count
 *
 * @method static \Database\Factories\EventPollFactory factory($count = null, $state = [])
 * @method static Builder<static>|EventPoll newModelQuery()
 * @method static Builder<static>|EventPoll newQuery()
 * @method static Builder<static>|EventPoll open()
 * @method static Builder<static>|EventPoll query()
 *
 * @mixin \Eloquent
 */
class EventPoll extends Model
{
    /** @use HasFactory<EventPollFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'event_id',
        'name',
        'type',
        'opens_at',
        'closes_at',
        'votes_per_player',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => PollType::class,
            'opens_at' => 'datetime',
            'closes_at' => 'datetime',
            'votes_per_player' => 'integer',
        ];
    }

    /**
     * Whether Ballots can be cast right now.
     *
     * The window is the stored timestamps, toggled by an Organiser and
     * independent of every other Poll.
     */
    public function isOpen(): bool
    {
        if ($this->opens_at === null || $this->opens_at->isFuture()) {
            return false;
        }

        return $this->closes_at === null || $this->closes_at->isFuture();
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeOpen(Builder $query): void
    {
        $query->whereNotNull('opens_at')
            ->where('opens_at', '<=', now())
            ->where(fn (Builder $query) => $query->whereNull('closes_at')->orWhere('closes_at', '>', now()));
    }

    /**
     * The Attendees this voter may pick.
     *
     * The Poll's type *is* the eligibility rule — nobody may pick their own
     * Attendee, and beyond that a painting Poll is limited to armies actually
     * on the display table while a favourite-opponent Poll is limited to teams
     * this voter has faced.
     *
     * @return Builder<EventAttendee>
     */
    public function eligibleSubjects(User $voter): Builder
    {
        $ownAttendeeIds = EventAttendeeMembership::query()
            ->where('event_id', $this->event_id)
            ->where('user_id', $voter->getKey())
            ->pluck('event_attendee_id');

        $subjects = EventAttendee::query()
            ->where('event_id', $this->event_id)
            ->whereNotIn('id', $ownAttendeeIds);

        return match ($this->type) {
            PollType::Painting => $subjects->where('painting_entered', true),
            PollType::FavouriteOpponent => $subjects->whereHas(
                'games',
                fn (Builder $query) => $query
                    ->where('games.is_bye', false)
                    ->whereIn('games.id', GameAttendeePivot::query()
                        ->whereIn('event_attendee_id', $ownAttendeeIds)
                        ->select('game_id')),
            ),
        };
    }

    /**
     * @return BelongsTo<Event, $this>
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * @return HasMany<EventVote, $this>
     */
    public function votes(): HasMany
    {
        return $this->hasMany(EventVote::class);
    }
}
