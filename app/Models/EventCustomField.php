<?php

namespace App\Models;

use App\Enums\CustomFieldType;
use Database\Factories\EventCustomFieldFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $event_id
 * @property string $name
 * @property CustomFieldType $type
 * @property array<int, string>|null $options
 * @property int $display_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Event $event
 * @property-read Collection<int, EventCustomFieldResponse> $responses
 *
 * @method static EventCustomFieldFactory factory($count = null, $state = [])
 */
class EventCustomField extends Model
{
    /** @use HasFactory<EventCustomFieldFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'event_id',
        'name',
        'type',
        'options',
        'display_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => CustomFieldType::class,
            'options' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Event, $this>
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * @return HasMany<EventCustomFieldResponse, $this>
     */
    public function responses(): HasMany
    {
        return $this->hasMany(EventCustomFieldResponse::class);
    }
}
