<?php

namespace App\Models;

use App\Enums\Country;
use Database\Factories\ClubFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $city
 * @property Country|null $country
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Event> $events
 * @property-read int|null $events_count
 * @property-read Collection<int, User> $members
 * @property-read int|null $members_count
 *
 * @method static \Database\Factories\ClubFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Club newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Club newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Club query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Club whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Club whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Club whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Club whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Club whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Club whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Club whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Club extends Model
{
    /** @use HasFactory<ClubFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'city',
        'country',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'country' => Country::class,
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    /**
     * @return HasMany<Event, $this>
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }
}
