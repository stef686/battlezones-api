<?php

namespace App\Models;

use Database\Factories\MessageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $conversation_id
 * @property int $user_id
 * @property string|null $body
 * @property Carbon|null $edited_at
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Conversation $conversation
 * @property-read User $user
 *
 * @mixin \Eloquent
 */
class Message extends Model
{
    /** @use HasFactory<MessageFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'body',
        'user_id',
        'conversation_id',
        'edited_at',
        'deleted_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'edited_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Conversation, $this>
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isEditable(): bool
    {
        return ! $this->isDeleted() && $this->created_at->diffInMinutes(now()) < 15;
    }

    public function isDeleted(): bool
    {
        return $this->deleted_at !== null;
    }
}
