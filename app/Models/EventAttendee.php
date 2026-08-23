<?php

namespace App\Models;

use App\Enums\Allegiance;
use Database\Factories\EventAttendeeFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $event_id
 * @property string|null $name
 * @property Allegiance|null $allegiance
 * @property Carbon|null $army_lists_revealed_at
 * @property Carbon|null $checked_in_at
 * @property bool $painting_entered
 * @property int|null $display_number
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, EventCustomFieldResponse> $customFieldResponses
 * @property-read int|null $custom_field_responses_count
 * @property-read Event $event
 * @property-read GameAttendeePivot|null $pivot
 * @property-read Collection<int, Game> $games
 * @property-read int|null $games_count
 * @property-read EventAttendeeMembership|null $membership
 * @property-read Collection<int, User> $members
 * @property-read int|null $members_count
 * @property-read Collection<int, EventAttendeeMembership> $memberships
 * @property-read int|null $memberships_count
 *
 * @method static \Database\Factories\EventAttendeeFactory factory($count = null, $state = [])
 * @method static Builder<static>|EventAttendee newModelQuery()
 * @method static Builder<static>|EventAttendee newQuery()
 * @method static Builder<static>|EventAttendee query()
 * @method static Builder<static>|EventAttendee whereAllegiance($value)
 * @method static Builder<static>|EventAttendee whereArmyListsRevealedAt($value)
 * @method static Builder<static>|EventAttendee whereCheckedInAt($value)
 * @method static Builder<static>|EventAttendee whereCreatedAt($value)
 * @method static Builder<static>|EventAttendee whereDisplayNumber($value)
 * @method static Builder<static>|EventAttendee whereEventId($value)
 * @method static Builder<static>|EventAttendee whereId($value)
 * @method static Builder<static>|EventAttendee whereName($value)
 * @method static Builder<static>|EventAttendee wherePaintingEntered($value)
 * @method static Builder<static>|EventAttendee whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class EventAttendee extends Model
{
    /** @use HasFactory<EventAttendeeFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'event_id',
        'name',
        'allegiance',
        'army_lists_revealed_at',
        'checked_in_at',
        'painting_entered',
        'display_number',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'allegiance' => Allegiance::class,
            'army_lists_revealed_at' => 'datetime',
            'checked_in_at' => 'datetime',
            'painting_entered' => 'boolean',
            'display_number' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Event, $this>
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * The Players competing as this Attendee: one for singles, two for doubles.
     *
     * @return BelongsToMany<User, $this, EventAttendeeMembership, 'membership'>
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'event_attendee_user')
            ->using(EventAttendeeMembership::class)
            ->as('membership')
            ->withPivot(['id', 'event_id', 'faction_id', 'army_list'])
            ->withTimestamps();
    }

    /**
     * @return HasMany<EventAttendeeMembership, $this>
     */
    public function memberships(): HasMany
    {
        return $this->hasMany(EventAttendeeMembership::class);
    }

    /**
     * Whether this party's army lists are open to the field.
     *
     * Every Player must have submitted, which is deliberate peer pressure. It
     * can deadlock on a partner who never opens their invite, so an Organiser
     * can reveal a team's lists regardless.
     */
    public function armyListsAreVisible(): bool
    {
        if ($this->army_lists_revealed_at !== null) {
            return true;
        }

        $memberships = $this->relationLoaded('memberships')
            ? $this->memberships
            : $this->memberships()->get();

        return $memberships->isNotEmpty()
            && $memberships->every(fn (EventAttendeeMembership $membership): bool => $membership->isArmyListLocked());
    }

    public function hasMember(User $user): bool
    {
        if ($this->relationLoaded('memberships')) {
            return $this->memberships->contains(
                fn (EventAttendeeMembership $membership): bool => $membership->user_id === $user->getKey()
            );
        }

        return $this->members()->whereKey($user->getKey())->exists();
    }

    /**
     * The name this Attendee competes under.
     *
     * Parties name themselves; a lone Player falls back to their own name.
     */
    public function displayName(): string
    {
        return $this->name ?? $this->memberships->first()?->user->public_name ?? '';
    }

    /**
     * @return HasMany<EventCustomFieldResponse, $this>
     */
    public function customFieldResponses(): HasMany
    {
        return $this->hasMany(EventCustomFieldResponse::class);
    }

    /**
     * @return BelongsToMany<Game, $this, GameAttendeePivot>
     */
    public function games(): BelongsToMany
    {
        return $this->belongsToMany(Game::class, 'game_attendee')
            ->using(GameAttendeePivot::class)
            ->withTimestamps();
    }

    /**
     * The Attendees this Attendee has actually faced.
     *
     * A Bye is not an opponent: nobody sat across the table, so it is neither
     * a rematch to avoid nor a candidate for a favourite-opponent vote.
     *
     * @return Builder<self>
     */
    public function opponents(): Builder
    {
        return self::query()
            ->whereKeyNot($this->getKey())
            ->whereHas('games', fn (Builder $query) => $query
                ->where('games.is_bye', false)
                ->whereIn('games.id', $this->games()->where('games.is_bye', false)->select('games.id')));
    }
}
