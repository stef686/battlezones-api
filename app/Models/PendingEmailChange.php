<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $email
 * @property string $token
 * @property Carbon $created_at
 * @property-read User $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingEmailChange newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingEmailChange newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingEmailChange query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingEmailChange whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingEmailChange whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingEmailChange whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingEmailChange whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PendingEmailChange whereUserId($value)
 *
 * @mixin \Eloquent
 */
class PendingEmailChange extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'email',
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
}
