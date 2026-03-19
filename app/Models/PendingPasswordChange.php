<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $password
 * @property string $token
 * @property Carbon $created_at
 * @property-read User $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingPasswordChange newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingPasswordChange newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingPasswordChange query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingPasswordChange whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingPasswordChange whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingPasswordChange wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingPasswordChange whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingPasswordChange whereUserId($value)
 *
 * @mixin \Eloquent
 */
class PendingPasswordChange extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'password',
        'token',
        'created_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return $this->created_at->addDay()->isPast();
    }
}
