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
 * @property-read Event $event
 * @property-read User $user
 * @property-read Faction|null $faction
 * @property-read Collection<int, EventCustomFieldResponse> $customFieldResponses
 * @property-read GameAttendeePivot|null $pivot
 *
 * @method static EventAttendeeFactory factory($count = null, $state = [])
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
