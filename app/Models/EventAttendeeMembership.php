<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Carbon;

/**
 * One Player's membership of an Attendee.
 *
 * Faction and army list belong to the Player rather than the party, so a
 * doubles team can field two different Factions under one Allegiance.
 *
 * @property int $id
 * @property int $event_attendee_id
 * @property int $user_id
 * @property int $event_id
 * @property int|null $faction_id
 * @property string|null $army_list
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Faction|null $faction
 * @property-read User $user
 *
 * @mixin \Eloquent
 */
class EventAttendeeMembership extends Pivot
{
    protected $table = 'event_attendee_user';

    public $incrementing = true;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'event_attendee_id',
        'user_id',
        'event_id',
        'faction_id',
        'army_list',
    ];

    /**
     * @return BelongsTo<Faction, $this>
     */
    public function faction(): BelongsTo
    {
        return $this->belongsTo(Faction::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
