<?php

namespace App\Models;

use Database\Factories\GameSystemFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Event> $events
 * @property-read int|null $events_count
 * @property-read Collection<int, Faction> $factions
 * @property-read int|null $factions_count
 *
 * @method static \Database\Factories\GameSystemFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameSystem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameSystem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameSystem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameSystem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameSystem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameSystem whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameSystem whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GameSystem whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class GameSystem extends Model
{
    /** @use HasFactory<GameSystemFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * @return HasMany<Faction, $this>
     */
    public function factions(): HasMany
    {
        return $this->hasMany(Faction::class);
    }

    /**
     * @return HasMany<Event, $this>
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }
}
