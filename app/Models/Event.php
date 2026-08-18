<?php

namespace App\Models;

use App\Casts\EventSettings;
use App\Enums\Country;
use App\Enums\EventStatus;
use App\Enums\PairingFormat;
use App\Enums\RegistrationMode;
use Database\Factories\EventFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
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
 * @property int $attendee_size
 * @property RegistrationMode $registration_mode
 * @property Carbon|null $registration_closes_at
 * @property string $timezone
 * @property EventSettings $settings
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, EventAttendee> $attendees
 * @property-read int|null $attendees_count
 * @property-read Club|null $club
 * @property-read Collection<int, EventDocument> $documents
 * @property-read int|null $documents_count
 * @property-read GameSystem $gameSystem
 * @property-read Collection<int, Photo> $photos
 * @property-read int|null $photos_count
 * @property-read Collection<int, Round> $rounds
 * @property-read int|null $rounds_count
 * @property-read Collection<int, EventScoreType> $scoreTypes
 * @property-read int|null $score_types_count
 * @property-read Collection<int, EventStanding> $standings
 * @property-read int|null $standings_count
 * @property-read Collection<int, EventUpdate> $updates
 * @property-read int|null $updates_count
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
 * @method static Builder<static>|Event whereSettings($value)
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
        'attendee_size',
        'registration_mode',
        'registration_closes_at',
        'timezone',
        'settings',
        'standings_visible',
    ];

    /**
     * Settings are internal configuration read through the typed DTO, so they
     * are kept out of the model's array form; the toggle-able parts are
     * appended individually instead.
     *
     * @var list<string>
     */
    protected $hidden = ['settings'];

    /**
     * @var list<string>
     */
    protected $appends = ['standings_visible'];

    /**
     * Defaults that apply in memory as well as on insert.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'attendee_size' => 1,
        'registration_mode' => RegistrationMode::Open->value,
        'timezone' => 'UTC',
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
            'registration_closes_at' => 'datetime',
            'registration_mode' => RegistrationMode::class,
            'attendee_size' => 'integer',
            'settings' => EventSettings::class,
        ];
    }

    /**
     * Whether Standings are visible to Players.
     *
     * Backed by settings rather than a column of its own, but exposed as an
     * attribute so admin forms and seeders can read and write it by name.
     *
     * @return Attribute<bool, EventSettings>
     */
    protected function standingsVisible(): Attribute
    {
        return Attribute::make(
            get: fn (): bool => $this->settings->standingsVisible,
            set: fn (bool $visible): array => [
                'settings' => $this->settings->with(['standings_visible' => $visible]),
            ],
        );
    }

    /**
     * Whether entry to this Event has closed.
     *
     * A null deadline means registration stays open until an Organiser closes
     * it by hand, never that it is already closed.
     */
    public function registrationHasClosed(): bool
    {
        return $this->registration_closes_at !== null
            && $this->registration_closes_at->isPast();
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
     * @return HasMany<EventCustomField, $this>
     */
    public function customFields(): HasMany
    {
        return $this->hasMany(EventCustomField::class);
    }

    /**
     * @return HasMany<EventDocument, $this>
     */
    public function documents(): HasMany
    {
        return $this->hasMany(EventDocument::class);
    }

    /**
     * @return HasMany<Photo, $this>
     */
    public function photos(): HasMany
    {
        return $this->hasMany(Photo::class);
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

    /**
     * @return HasMany<EventScoreType, $this>
     */
    public function scoreTypes(): HasMany
    {
        return $this->hasMany(EventScoreType::class);
    }

    /**
     * @return HasMany<EventStanding, $this>
     */
    public function standings(): HasMany
    {
        return $this->hasMany(EventStanding::class);
    }
}
