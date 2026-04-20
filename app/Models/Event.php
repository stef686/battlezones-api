<?php

namespace App\Models;

use App\Enums\Country;
use App\Enums\EventStatus;
use App\Enums\PairingFormat;
use Database\Factories\EventFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $game_system_id
 * @property int|null $club_id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property EventStatus $status
 * @property PairingFormat $pairing_format
 * @property Carbon $starts_at
 * @property Carbon $ends_at
 * @property string|null $venue_name
 * @property string|null $venue_address
 * @property string|null $venue_city
 * @property Country|null $venue_country
 * @property int|null $max_attendees
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Club|null $club
 * @property-read Collection<int, EventDocument> $documents
 * @property-read int|null $documents_count
 * @property-read Collection<int, EventUpdate> $updates
 * @property-read int|null $updates_count
 * @property-read GameSystem $gameSystem
 *
 * @method static \Database\Factories\EventFactory factory($count = null, $state = [])
 * @method static Builder<static>|Event newModelQuery()
 * @method static Builder<static>|Event newQuery()
 * @method static Builder<static>|Event publiclyVisible()
 * @method static Builder<static>|Event query()
 * @method static Builder<static>|Event whereClubId($value)
 * @method static Builder<static>|Event whereCreatedAt($value)
 * @method static Builder<static>|Event whereDescription($value)
 * @method static Builder<static>|Event whereEndsAt($value)
 * @method static Builder<static>|Event whereGameSystemId($value)
 * @method static Builder<static>|Event whereId($value)
 * @method static Builder<static>|Event whereMaxAttendees($value)
 * @method static Builder<static>|Event whereName($value)
 * @method static Builder<static>|Event wherePairingFormat($value)
 * @method static Builder<static>|Event whereSlug($value)
 * @method static Builder<static>|Event whereStartsAt($value)
 * @method static Builder<static>|Event whereStatus($value)
 * @method static Builder<static>|Event whereUpdatedAt($value)
 * @method static Builder<static>|Event whereVenueAddress($value)
 * @method static Builder<static>|Event whereVenueCity($value)
 * @method static Builder<static>|Event whereVenueCountry($value)
 * @method static Builder<static>|Event whereVenueName($value)
 *
 * @mixin \Eloquent
 */
class Event extends Model
{
    /** @use HasFactory<EventFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'game_system_id',
        'club_id',
        'name',
        'slug',
        'description',
        'status',
        'pairing_format',
        'starts_at',
        'ends_at',
        'venue_name',
        'venue_address',
        'venue_city',
        'venue_country',
        'max_attendees',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => EventStatus::class,
            'pairing_format' => PairingFormat::class,
            'venue_country' => Country::class,
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopePubliclyVisible(Builder $query): void
    {
        $query->whereIn('status', array_map(
            fn (EventStatus $status): string => $status->value,
            array_filter(EventStatus::cases(), fn (EventStatus $status): bool => $status->isPubliclyVisible()),
        ));
    }

    /**
     * @return BelongsTo<GameSystem, $this>
     */
    public function gameSystem(): BelongsTo
    {
        return $this->belongsTo(GameSystem::class);
    }

    /**
     * @return BelongsTo<Club, $this>
     */
    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    /**
     * @return HasMany<EventDocument, $this>
     */
    public function documents(): HasMany
    {
        return $this->hasMany(EventDocument::class);
    }

    /**
     * @return HasMany<EventUpdate, $this>
     */
    public function updates(): HasMany
    {
        return $this->hasMany(EventUpdate::class);
    }

    /**
     * @return HasMany<EventAttendee, $this>
     */
    public function attendees(): HasMany
    {
        return $this->hasMany(EventAttendee::class);
    }

    /**
     * @return HasMany<Round, $this>
     */
    public function rounds(): HasMany
    {
        return $this->hasMany(Round::class);
    }
}
