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
 * @property Carbon|null $army_list_submitted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Faction|null $faction
 * @property-read User $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventAttendeeMembership newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventAttendeeMembership newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventAttendeeMembership query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventAttendeeMembership whereArmyList($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventAttendeeMembership whereArmyListSubmittedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventAttendeeMembership whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventAttendeeMembership whereEventAttendeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventAttendeeMembership whereEventId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventAttendeeMembership whereFactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventAttendeeMembership whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventAttendeeMembership whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventAttendeeMembership whereUserId($value)
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
        'army_list_submitted_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'army_list_submitted_at' => 'datetime',
        ];
    }

    /**
     * Whether this Player's list is read-only.
     *
     * A submitted list is final because opponents prepare against it; only an
     * Organiser can reopen one, and only to correct a mistake.
     */
    public function isArmyListLocked(): bool
    {
        return $this->army_list_submitted_at !== null;
    }

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
