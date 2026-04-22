<?php

namespace App\Models;

use Database\Factories\EventStandingFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $event_id
 * @property int $event_attendee_id
 * @property int $position
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read EventAttendee $attendee
 * @property-read Event $event
 * @property-read Collection<int, EventStandingScore> $scores
 * @property-read int|null $scores_count
 *
 * @method static \Database\Factories\EventStandingFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventStanding newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventStanding newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventStanding query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventStanding whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventStanding whereEventAttendeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventStanding whereEventId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventStanding whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventStanding wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventStanding whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class EventStanding extends Model
{
    /** @use HasFactory<EventStandingFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'event_id',
        'event_attendee_id',
        'position',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'position' => 'integer',
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
     * @return BelongsTo<EventAttendee, $this>
     */
    public function attendee(): BelongsTo
    {
        return $this->belongsTo(EventAttendee::class, 'event_attendee_id');
    }

    /**
     * @return HasMany<EventStandingScore, $this>
     */
    public function scores(): HasMany
    {
        return $this->hasMany(EventStandingScore::class);
    }
}
