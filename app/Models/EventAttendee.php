<?php

namespace App\Models;

use Database\Factories\EventAttendeeFactory;
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
 * @property int $user_id
 * @property int|null $faction_id
 * @property string|null $army_list
 * @property Carbon|null $checked_in_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, EventCustomFieldResponse> $customFieldResponses
 * @property-read int|null $custom_field_responses_count
 * @property-read Event $event
 * @property-read Faction|null $faction
 * @property-read GameAttendeePivot|null $pivot
 * @property-read Collection<int, Game> $games
 * @property-read int|null $games_count
 * @property-read User $user
 *
 * @method static \Database\Factories\EventAttendeeFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventAttendee newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventAttendee newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventAttendee query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventAttendee whereArmyList($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventAttendee whereCheckedInAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventAttendee whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventAttendee whereEventId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventAttendee whereFactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventAttendee whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventAttendee whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventAttendee whereUserId($value)
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
        'user_id',
        'faction_id',
        'army_list',
        'checked_in_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'checked_in_at' => 'datetime',
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
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Faction, $this>
     */
    public function faction(): BelongsTo
    {
        return $this->belongsTo(Faction::class);
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
            ->withPivot('score')
            ->withTimestamps();
    }
}
