<?php

namespace App\Models;

use App\Enums\PollType;
use App\Enums\ScheduleBlockType;
use Database\Factories\EventScheduleBlockFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * One entry in an Event's schedule.
 *
 * @property int $id
 * @property int $event_id
 * @property string $label
 * @property ScheduleBlockType $type
 * @property int|null $round_id
 * @property Carbon $starts_at
 * @property Carbon $ends_at
 * @property int $display_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Event $event
 * @property-read Round|null $round
 *
 * @method static \Database\Factories\EventScheduleBlockFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventScheduleBlock newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventScheduleBlock newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventScheduleBlock query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventScheduleBlock whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventScheduleBlock whereDisplayOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventScheduleBlock whereEndsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventScheduleBlock whereEventId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventScheduleBlock whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventScheduleBlock whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventScheduleBlock whereRoundId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventScheduleBlock whereStartsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventScheduleBlock whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EventScheduleBlock whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class EventScheduleBlock extends Model
{
    /** @use HasFactory<EventScheduleBlockFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'event_id',
        'label',
        'type',
        'round_id',
        'starts_at',
        'ends_at',
        'display_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => ScheduleBlockType::class,
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'display_order' => 'integer',
        ];
    }

    /**
     * The day this block falls on, in the Event's own timezone.
     *
     * Derived rather than stored: a UK Event in October would get away with
     * UTC, but a late-evening block in July lands on the wrong date, and an
     * Organiser editing a time must not leave the block filed under it.
     */
    public function day(): string
    {
        return $this->starts_at->copy()->setTimezone($this->event->timezone)->toDateString();
    }

    /**
     * The row this block points at, if its type points at one.
     *
     * A round block carries its Round, a painting block the Poll whose window
     * it describes, and an info block nothing at all.
     */
    public function targetId(): ?int
    {
        return match ($this->type) {
            ScheduleBlockType::Info => null,
            ScheduleBlockType::Round => $this->round_id,
            ScheduleBlockType::PaintingVoting => $this->event->latestPoll(PollType::Painting)?->getKey(),
        };
    }

    /**
     * Whether the thing this block describes is happening now.
     *
     * The API says live or not and hands back an id; where that leads is the
     * front end's routing decision, not something to hard-code into a payload.
     */
    public function isTargetLive(): bool
    {
        return match ($this->type) {
            ScheduleBlockType::Info => false,
            ScheduleBlockType::Round => $this->round?->isLive() ?? false,
            ScheduleBlockType::PaintingVoting => $this->event->openPoll(PollType::Painting) !== null,
        };
    }

    /**
     * @return BelongsTo<Event, $this>
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * @return BelongsTo<Round, $this>
     */
    public function round(): BelongsTo
    {
        return $this->belongsTo(Round::class);
    }
}
