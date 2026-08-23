<?php

namespace App\Models;

use App\Enums\Country;
use App\Enums\NotificationChannel;
use App\Enums\NotificationType;
use App\Enums\PrivacyOption;
use App\Notifications\Auth\ResetPasswordNotification;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * @property int $id
 * @property string $name
 * @property string|null $username
 * @property Country|null $country
 * @property bool $show_public_name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property Carbon|null $claimed_at
 * @property string|null $password
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property array<array-key, mixed>|null $notification_settings
 * @property array<array-key, mixed>|null $privacy_settings
 * @property bool $is_admin
 * @property-read Collection<int, User> $blockedBy
 * @property-read int|null $blocked_by_count
 * @property-read Collection<int, User> $blockedUsers
 * @property-read int|null $blocked_users_count
 * @property-read Collection<int, Club> $clubs
 * @property-read int|null $clubs_count
 * @property-read Collection<int, Conversation> $conversations
 * @property-read int|null $conversations_count
 * @property-read Collection<int, EventAttendee> $eventAttendees
 * @property-read int|null $event_attendees_count
 * @property-read Collection<int, User> $followers
 * @property-read int|null $followers_count
 * @property-read Collection<int, User> $following
 * @property-read int|null $following_count
 * @property-read DatabaseNotificationCollection<int, DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read PendingEmailChange|null $pendingEmailChange
 * @property-read PendingPasswordChange|null $pendingPasswordChange
 * @property-read Collection<int, Photo> $photos
 * @property-read int|null $photos_count
 * @property-read string $public_name
 * @property-read Collection<int, PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 *
 * @method static Builder<static>|User claimed()
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static Builder<static>|User newModelQuery()
 * @method static Builder<static>|User newQuery()
 * @method static Builder<static>|User query()
 * @method static Builder<static>|User whereClaimedAt($value)
 * @method static Builder<static>|User whereCountry($value)
 * @method static Builder<static>|User whereCreatedAt($value)
 * @method static Builder<static>|User whereEmail($value)
 * @method static Builder<static>|User whereEmailVerifiedAt($value)
 * @method static Builder<static>|User whereId($value)
 * @method static Builder<static>|User whereIsAdmin($value)
 * @method static Builder<static>|User whereName($value)
 * @method static Builder<static>|User whereNotificationSettings($value)
 * @method static Builder<static>|User wherePassword($value)
 * @method static Builder<static>|User wherePrivacySettings($value)
 * @method static Builder<static>|User whereRememberToken($value)
 * @method static Builder<static>|User whereShowPublicName($value)
 * @method static Builder<static>|User whereUpdatedAt($value)
 * @method static Builder<static>|User whereUsername($value)
 *
 * @mixin \Eloquent
 */
class User extends Authenticatable implements FilamentUser, MustVerifyEmail
{
    use HasApiTokens;

    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'country',
        'show_public_name',
        'email',
        'password',
        'claimed_at',
        'notification_settings',
        'privacy_settings',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'claimed_at' => 'datetime',
            'country' => Country::class,
            'is_admin' => 'boolean',
            'show_public_name' => 'boolean',
            'notification_settings' => 'array',
            'privacy_settings' => 'array',
        ];
    }

    protected function publicName(): Attribute
    {
        return Attribute::get(function (): string {
            if (! $this->show_public_name && $this->username) {
                return $this->username;
            }

            return $this->name;
        });
    }

    /**
     * @return BelongsToMany<Conversation, $this>
     */
    public function conversations(): BelongsToMany
    {
        return $this->belongsToMany(Conversation::class)
            ->withPivot('last_read_at', 'deleted_at', 'archived_at')
            ->withTimestamps();
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function following(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'follows', 'follower_id', 'following_id');
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'follows', 'following_id', 'follower_id');
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function blockedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'blocks', 'blocker_id', 'blocked_id');
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function blockedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'blocks', 'blocked_id', 'blocker_id');
    }

    public function hasBlocked(User $user): bool
    {
        return $this->blockedUsers()->where('blocked_id', $user->id)->exists();
    }

    public function isBlockedBy(User $user): bool
    {
        return $user->hasBlocked($this);
    }

    /**
     * Get all user IDs blocked by or blocking this user (both directions).
     * Memoized per-request to avoid repeated queries on followers/following lists.
     *
     * @return \Illuminate\Support\Collection<int, int>
     */
    public function allBlockedIds(): \Illuminate\Support\Collection
    {
        return once(fn () => $this->blockedUsers()->select('blocked_id as user_id')
            ->union($this->blockedBy()->select('blocker_id as user_id')->getQuery())
            ->pluck('user_id'));
    }

    /**
     * @return HasMany<Photo, $this>
     */
    public function photos(): HasMany
    {
        return $this->hasMany(Photo::class);
    }

    /**
     * @return BelongsToMany<Club, $this>
     */
    public function clubs(): BelongsToMany
    {
        return $this->belongsToMany(Club::class)->withTimestamps();
    }

    /**
     * @return HasMany<EventAttendee, $this>
     */
    public function eventAttendees(): HasMany
    {
        return $this->hasMany(EventAttendee::class);
    }

    /**
     * @return HasOne<PendingEmailChange, $this>
     */
    public function pendingEmailChange(): HasOne
    {
        return $this->hasOne(PendingEmailChange::class);
    }

    /**
     * @return HasOne<PendingPasswordChange, $this>
     */
    public function pendingPasswordChange(): HasOne
    {
        return $this->hasOne(PendingPasswordChange::class);
    }

    /**
     * Whether this Player has turned their invited account into a real one.
     *
     * An unclaimed account exists because someone else entered their email;
     * until they set a password it is reachable only through an emailed
     * credential, so it stays out of public surfaces and cannot authenticate.
     */
    public function isClaimed(): bool
    {
        return $this->claimed_at !== null;
    }

    /**
     * Accounts that exist by their owner's own doing.
     *
     * @param  Builder<self>  $query
     */
    public function scopeClaimed(Builder $query): void
    {
        $query->whereNotNull('users.claimed_at');
    }

    /**
     * Unclaimed accounts are not addressable by route.
     *
     * Someone else's invitation created them, so until they are claimed they
     * have no public presence to link to, follow, or read a profile from.
     * Enforcing it at binding keeps every current and future /users route
     * honest rather than relying on each one to remember.
     *
     * @param  mixed  $value
     * @param  string|null  $field
     */
    public function resolveRouteBinding($value, $field = null): ?Model
    {
        $user = parent::resolveRouteBinding($value, $field);

        return $user instanceof self && $user->isClaimed() ? $user : null;
    }

    /**
     * @return list<NotificationChannel>
     */
    public function getNotificationChannels(NotificationType $type): array
    {
        $channels = $this->notification_settings[$type->value] ?? null;

        if ($channels === null) {
            return [NotificationChannel::Email];
        }

        return array_map(
            fn (string $channel): NotificationChannel => NotificationChannel::from($channel),
            $channels,
        );
    }

    /**
     * The notification drivers to deliver a given type on.
     *
     * Channels the application has no registered driver for are dropped, so a
     * preference for a channel that is not built yet cannot break delivery on
     * the channels that are. Event notifications always keep the database
     * driver on top of whatever the preference asks for.
     *
     * @return list<string>
     */
    public function getNotificationDrivers(NotificationType $type): array
    {
        $drivers = array_map(
            fn (NotificationChannel $channel): ?string => $channel->driver(),
            $this->getNotificationChannels($type),
        );

        $drivers = array_values(array_filter($drivers));

        return $type->alwaysInApp()
            ? array_values(array_unique(['database', ...$drivers]))
            : $drivers;
    }

    public function getMessagingPrivacy(): PrivacyOption
    {
        return $this->getPrivacySetting('messaging');
    }

    public function getProfilePrivacy(): PrivacyOption
    {
        return $this->getPrivacySetting('profile');
    }

    private function getPrivacySetting(string $key): PrivacyOption
    {
        $value = $this->privacy_settings[$key] ?? null;

        return $value ? PrivacyOption::from($value) : PrivacyOption::Anyone;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_admin;
    }

    public function sendPasswordResetNotification(#[\SensitiveParameter] $token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}
