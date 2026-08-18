<?php

namespace App\Models;

use App\Enums\EventInviteRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A time-limited credential emailed to one person for one Event.
 *
 * Deliberately not a magic link: a magic link removes the need for an account,
 * whereas an Invite exists to expire and push its holder into claiming one.
 *
 * @property int $id
 * @property int $event_id
 * @property int $user_id
 * @property int|null $event_attendee_id
 * @property int|null $invited_by_id
 * @property EventInviteRole $role
 * @property string|null $token
 * @property Carbon $expires_at
 * @property Carbon|null $revoked_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read EventAttendee|null $attendee
 * @property-read Event $event
 * @property-read User|null $invitedBy
 * @property-read User $user
 *
 * @method static Builder<static>|EventInvite newModelQuery()
 * @method static Builder<static>|EventInvite newQuery()
 * @method static Builder<static>|EventInvite outstanding()
 * @method static Builder<static>|EventInvite query()
 * @method static Builder<static>|EventInvite whereCreatedAt($value)
 * @method static Builder<static>|EventInvite whereEventAttendeeId($value)
 * @method static Builder<static>|EventInvite whereEventId($value)
 * @method static Builder<static>|EventInvite whereExpiresAt($value)
 * @method static Builder<static>|EventInvite whereId($value)
 * @method static Builder<static>|EventInvite whereInvitedById($value)
 * @method static Builder<static>|EventInvite whereRevokedAt($value)
 * @method static Builder<static>|EventInvite whereRole($value)
 * @method static Builder<static>|EventInvite whereToken($value)
 * @method static Builder<static>|EventInvite whereUpdatedAt($value)
 * @method static Builder<static>|EventInvite whereUserId($value)
 *
 * @mixin \Eloquent
 */
class EventInvite extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'event_id',
        'user_id',
        'event_attendee_id',
        'invited_by_id',
        'role',
        'token',
        'expires_at',
        'revoked_at',
    ];

    /**
     * The token is a credential, so it never reaches a serialised response.
     *
     * @var list<string>
     */
    protected $hidden = ['token'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'role' => EventInviteRole::class,
            'expires_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    /**
     * The stored form of a plain token.
     *
     * Deterministic rather than salted, because the token is looked up by its
     * hash; it is high-entropy random, so there is nothing to guess offline.
     */
    public static function hashToken(#[\SensitiveParameter] string $plainToken): string
    {
        return hash('sha256', $plainToken);
    }

    public static function findByToken(#[\SensitiveParameter] string $plainToken): ?self
    {
        return static::query()->where('token', static::hashToken($plainToken))->first();
    }

    /**
     * Invites still capable of granting access.
     *
     * @param  Builder<self>  $query
     */
    public function scopeOutstanding(Builder $query): void
    {
        $query->whereNull('revoked_at')->where('expires_at', '>', now());
    }

    public function hasExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isRevoked(): bool
    {
        return $this->revoked_at !== null;
    }

    public function isUsable(): bool
    {
        return ! $this->hasExpired() && ! $this->isRevoked();
    }

    public function revoke(): void
    {
        $this->forceFill(['revoked_at' => now()])->save();
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
     * @return BelongsTo<EventAttendee, $this>
     */
    public function attendee(): BelongsTo
    {
        return $this->belongsTo(EventAttendee::class, 'event_attendee_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by_id');
    }
}
