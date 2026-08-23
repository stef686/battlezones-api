<?php

namespace App\Models;

use Database\Factories\FeedbackInvitationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A Player's one-time way into the feedback form.
 *
 * Not an Invite: Invites die two days after the Event and would quietly
 * re-grant Event access, neither of which a feedback link should do.
 *
 * @property int $id
 * @property int $event_id
 * @property int $user_id
 * @property string $token
 * @property Carbon $sent_at
 * @property Carbon $expires_at
 * @property Carbon|null $submitted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Event $event
 * @property-read User $user
 *
 * @method static \Database\Factories\FeedbackInvitationFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeedbackInvitation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeedbackInvitation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeedbackInvitation query()
 *
 * @mixin \Eloquent
 */
class FeedbackInvitation extends Model
{
    /** @use HasFactory<FeedbackInvitationFactory> */
    use HasFactory;

    /**
     * How long a feedback link lives, from the moment it is sent.
     */
    public const LIFETIME_DAYS = 30;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'event_id',
        'user_id',
        'token',
        'sent_at',
        'expires_at',
        'submitted_at',
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
            'sent_at' => 'datetime',
            'expires_at' => 'datetime',
            'submitted_at' => 'datetime',
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

    public function isUsable(): bool
    {
        return $this->submitted_at === null && $this->expires_at->isFuture();
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
}
