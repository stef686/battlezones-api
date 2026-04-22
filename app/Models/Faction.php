<?php

namespace App\Models;

use Database\Factories\FactionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $game_system_id
 * @property string $name
 * @property string $slug
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read GameSystem $gameSystem
 *
 * @method static \Database\Factories\FactionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Faction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Faction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Faction query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Faction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Faction whereGameSystemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Faction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Faction whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Faction whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Faction whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Faction extends Model
{
    /** @use HasFactory<FactionFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'game_system_id',
        'name',
        'slug',
    ];

    /**
     * @return BelongsTo<GameSystem, $this>
     */
    public function gameSystem(): BelongsTo
    {
        return $this->belongsTo(GameSystem::class);
    }
}
